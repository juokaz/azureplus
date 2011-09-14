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
    
    public function __construct(Storage $client, $app_file, $filesystem, $temp_folder, $git)
    {
        $this->client = $client;
        $this->app_file = $app_file;
        $this->temp_folder = $temp_folder;
        $this->filesystem = $filesystem;
        $this->git = $git;
    }
    
    public function deploy(App $app, $folder)
    {
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

        $folder = $this->temp_folder . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR . $app->getName();

        if ($this->git->checkout($app, $folder)) {
            $this->deploy($app, $folder);
            return true;
        } else {
            return false;
        }
    }
}
