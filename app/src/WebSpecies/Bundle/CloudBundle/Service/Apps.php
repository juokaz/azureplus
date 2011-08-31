<?php

namespace WebSpecies\Bundle\CloudBundle\Service;

use WebSpecies\Bundle\CloudBundle\Entity\App;

class Apps
{
    private $storage;
    private $azure;
    private $base_collection;
    private $base_file;
    private $app_file;
    
    public function __construct(Storage $storage, Azure $azure, $base_collection, $base_file, $app_file, $configuration_template)
    {
        $this->storage = $storage;
        $this->azure = $azure;
        $this->base_collection = $base_collection;
        $this->base_file = $base_file;
        $this->app_file = $app_file;
        $this->configuration_template = $configuration_template;
    }

    /**
     * Create a new app
     *
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @return \WebSpecies\Bundle\CloudBundle\Entity\App
     */
    public function createApp(App $app)
    {
        if ($app->getStatus() != App::STATUS_NEW) {
            throw new \InvalidArgumentException('Only new apps can be created');
        }

        // update app status
        $app->setStatus(App::STATUS_CREATING);

        // create hosted service
        $this->azure->createServer($app->getName());

        return $app;
    }

    /**
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @param bool $force
     * @return \WebSpecies\Bundle\CloudBundle\Entity\App
     */
    public function setupApp(App $app, $force = false)
    {
        if (!$force && $app->getStatus() != App::STATUS_CREATING) {
            throw new \InvalidArgumentException('App is already set up');
        }

        $container = $app->getContainer();

        if (!$this->storage->containerExists($container)) {
            // create container for app code
            $this->storage->createContainer($container);

            // create and retrieve new storage identifier
            $identifier = $this->storage->setIdentifier($container);

            // store identifier in app instance
            $app->setStorageIdentifier($identifier);
        }

        // get signed URL for code downloads
        $app_url = $this->storage->getSignerUrl($container, $this->app_file, $app->getStorageIdentifier());

        // app specific settings
		$conf = file_get_contents($this->configuration_template);
		$conf = str_replace('%APP_URL%', str_replace('&', '&amp;', $app_url), $conf);

        // base package url
        $package = $this->storage->getUrl($this->base_collection, $this->base_file);

        // create deployment
        $this->azure->createDeployment($app->getName(), $package, $conf);

        // set url
        $app->setUrl($this->azure->getUrl($app->getName()));

        return $app;
    }

    /**
     * Delete app
     *
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @return bool
     */
    public function deleteApp(App $app)
    {
        // Delete hosted service and deployment
        $this->azure->deleteServer($app->getName());

        // Delete app container
        $this->storage->deleteContainer($app->getContainer());

        return true;
    }

    /**
     * Is app deployed?
     *
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @return bool
     */
    public function isDeployed(App $app)
    {
        return $this->azure->isDeployed($app->getName());
    }
}