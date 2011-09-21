<?php

namespace WebSpecies\Bundle\CloudBundle\Service;

use WebSpecies\Bundle\CloudBundle\Entity\App;
use WebSpecies\Bundle\CloudBundle\Service\Internal\Storage;
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
    
    private $web_config;
    
    public function __construct(Storage $client, $app_file, $filesystem, $temp_folder, $git, $web_config)
    {
        $this->client = $client;
        $this->app_file = $app_file;
        $this->temp_folder = $temp_folder;
        $this->filesystem = $filesystem;
        $this->git = $git;
        $this->web_config = $web_config;
    }

    /**
     * Deploy app
     *
     * @throws \Exception|\InvalidArgumentException|\RuntimeException
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @param string $folder
     * @return string
     */
    public function deploy(App $app, $folder)
    {
        if (!$app->isLive()) {
            throw new \InvalidArgumentException('App cannot be deployed, because it\'s not live yet');
        }
        
        $container = $app->getContainer();
		$folder = rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		$temp_file = tempnam($this->temp_folder, 'Azure');

		$zip = new \ZipArchive();

		// open archive 
		if ($zip->open($temp_file, \ZIPARCHIVE::CREATE) !== TRUE) {
			throw new \RuntimeException ("Could not open archive");
		}

		// initialize an iterator
		// pass it the directory to be processed
		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS));

		// iterate over the directory
		// add each file found to the archive
		foreach ($iterator as $key=>$value) {
			// fix file name
			$name_ = str_replace($folder, '', $key);

            // check for ignored files and folders
            foreach (array('.git') as $pattern) {
                if (strpos($name_, $pattern) !== false) {
                    continue 2;
                }
            }

			if (!$zip->addFile(realpath($key), $name_)) {
			    throw new \RuntimeException ("ERROR: Could not add file: $key");
		    }
		}

        // Add web.config file
        $zip->addFromString('web.config', $this->getWebConfig($app));

		// close and save archive
		$zip->close();
		
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

        $this->deploy($app, $folder);
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
     * Get app folder to deploy to
     *
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @return bool
     */
    private function getAppFolder(App $app)
    {
        return $this->temp_folder . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR . $app->getName();
    }

    /**
     * Get IIS web.config file to the package
     *
     * @param \ZipArchive $zip
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @return void
     */
    private function getWebConfig(App $app)
    {
        $router = $app->getConfiguration()->getRouter();
        $public = $app->getConfiguration()->getPublicFolder();

        $template = file_get_contents($this->web_config);
        $template = str_replace('%PUBLIC_FOLDER%', $public, $template);

        if ($router) {
            $template = str_replace('%ROUTER_ENABLE%', 'true', $template);
        } else {
            $template = str_replace('%ROUTER_ENABLE%', 'false', $template);
        }

        // some apps might not have a router file, but should still have a default document
        $index = $router ?: 'index.php';

        $template = str_replace('%INDEX_FILE%', $index, $template);

        $template = str_replace('%PHP_PATH%', $app->getConfiguration()->getPhpRoot(), $template);

        return $template;
    }
}
