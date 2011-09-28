<?php

namespace WebSpecies\Bundle\CloudBundle\Service;

use WebSpecies\Bundle\CloudBundle\Entity\App;
use WebSpecies\Bundle\CloudBundle\Entity\Log;
use WebSpecies\Bundle\CloudBundle\Service\Internal\Storage;
use WebSpecies\Bundle\CloudBundle\Service\Internal\Packager;
use WebSpecies\Bundle\CloudBundle\Service\Source\Git;
use Symfony\Component\HttpKernel\Util\Filesystem;

class Deploy
{
    private $client;
    
    private $app_file;
    
    /**
     * @var \Symfony\Component\HttpKernel\Util\Filesystem
     */
    private $filesystem;

    private $temp_folder;

    /**
     * @var \WebSpecies\Bundle\CloudBundle\Service\Source\Git
     */
    private $git;

    /**
     * @var \WebSpecies\Bundle\CloudBundle\Service\Internal\Packager
     */
    private $packager;

    private $deploy_template;
    
    public function __construct(Storage $client, $app_file, Packager $packager, $filesystem, $temp_folder, $git, $deploy_template)
    {
        $this->client = $client;
        $this->app_file = $app_file;
        $this->temp_folder = $temp_folder;
        $this->filesystem = $filesystem;
        $this->git = $git;
        $this->deploy_template = $deploy_template;
        $this->packager = $packager;
    }

    /**
     * Deploy app
     *
     * @throws \Exception|\InvalidArgumentException|\RuntimeException
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @param string|null $folder
     * @return string
     */
    public function deploy(App $app, $folder = null)
    {
        if (!$app->isLive()) {
            throw new \InvalidArgumentException('App cannot be deployed, because it\'s not live yet');
        }

        if (!$folder) {
            $folder = $this->getAppFolder($app);
        }
        
        $container = $app->getContainer();
		$folder = rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // temporary store an archive
		$temp_file = tempnam($this->temp_folder, 'Azure');

        // create package to deploy
        $this->packager->createPackage($app, $temp_file, $folder);
		
		// store contents
		try {
            $result = $this->client->store($container, $this->app_file, $temp_file);
            
    	    // no need for this anymore
		    $this->filesystem->remove($temp_file);
        } catch (\Exception $e) {
		    // no need for this anymore
            $this->filesystem->remove($temp_file);
		    
		    throw $e;    
        }
		
		return $result;
    }

    /**
     * Deploy app from a checkout
     *
     * @throws \InvalidArgumentException
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @return bool
     */
    public function deployCheckout(App $app)
    {
        if (!$app->isAutoDeployable()) {
            throw new \InvalidArgumentException('App only supports direct deployments');
        }

        $folder = $this->getAppFolder($app);

        if ($this->git->checkout($app, $folder)) {
            $this->deploy($app, $folder);

            // add log
            $app->addLogMessage(sprintf('Deployed Git commit "%s"', $this->git->getCurrentCommitName($app, $folder)), Log::CAT_DEPLOY);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Deploy app from code archive
     *
     * @throws \InvalidArgumentException|\RuntimeException
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @param string $type
     * @param string $data
     * @return bool
     */
    public function deployArchive(App $app, $type, $data)
    {
        if ($app->isAutoDeployable()) {
            throw new \InvalidArgumentException('App only supports automatic deployments');
        }

        if ($type != 'application/zip') {
            throw new \InvalidArgumentException('Archive type not supported');
        }

        $folder = $this->getAppFolder($app);

        $zip = new \ZipArchive();

        $temp_file = tempnam(sys_get_temp_dir(), 'Azure');
        file_put_contents($temp_file, $data);

        // open archive
        if ($zip->open($temp_file) !== TRUE) {
			throw new \RuntimeException ("Could not open archive");
        }

        $zip->extractTo($folder);
        $zip->close();

        // No need for this anymore
        unlink($temp_file);

        // Deploy
        $this->deploy($app, $folder);

        // add log
        $app->addLogMessage('Deployed from direct upload', Log::CAT_DEPLOY);

        return true;
    }

    /**
     * Delete app folder
     *
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @return void
     */
    public function delete(App $app)
    {
        $this->filesystem->remove($this->getAppFolder($app));    
    }

    /**
     * Get deploy script template
     *
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @return string
     */
    public function getDeployScript(App $app, $endpoint)
    {
        $template = file_get_contents($this->deploy_template);
        $template = str_replace('%API_KEY%', $app->getKey(), $template);
        $template = str_replace('%ENDPOINT%', $endpoint, $template);

        return $template;
    }

    /**
     * Get app folder to deploy to
     *
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @return bool
     */
    private function getAppFolder(App $app)
    {
        return $this->temp_folder . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR . $app->getName();
    }
}
