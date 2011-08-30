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

    public function createApp(App $app)
    {
        $this->azure->createServer($app);

        $container = $app->getContainer();

        // create container for app code
        $this->storage->createContainer($container);

        // create and retrieve new storage identifier
        $identifier = $this->storage->setIdentifier($container);

        // store identifier in app instance
        $app->setStorageIdentifier($identifier);

        // get signed URL for code downloads
        $app_url = $this->storage->getSignerUrl($container, $this->app_file, $identifier);

        // app specific settings
		$conf = file_get_contents($this->configuration_template);
		$conf = str_replace('%APP_URL%', str_replace('&', '&amp;', $app_url), $conf);

        // base package url
        $package = $this->storage->getUrl($this->base_collection, $this->base_file);

        $this->azure->createDeployment($app, $package, $conf);

        while (!$this->isLive($app)) {
            sleep(1);
        }

        return $this->azure->getUrl($app);
    }

    /**
     * Is app Live
     *
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @return bool
     */
    public function isLive(App $app)
    {
        try {
            return $this->azure->getStatus($app) == 'Ready';
        } catch (\Exception $e) {
            return false;
        }
    }
}