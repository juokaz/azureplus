<?php

namespace WebSpecies\Bundle\CloudBundle\Service;

use WebSpecies\Bundle\CloudBundle\Document\App;

class Deploy
{
    private $client;
    private $app_file;
    
    public function __construct(\Microsoft_WindowsAzure_Storage_Blob $client, $app_file)
    {
        $this->client = $client;
        $this->app_file = $app_file;
    }
    
    public function deploy(App $app, $folder)
    {
        $container = $app->getContainer();
		$folder = rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		$temp_file = tempnam(sys_get_temp_dir(), 'Azure');

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
			
			if (!$zip->addFile(realpath($key), $name_)) {
			    throw new \RuntimeException ("ERROR: Could not add file: $key");
		    }
		}

		// close and save archive
		$zip->close();

        // check if container is in place
		if (!$this->client->containerExists($container)) {
			throw new \RuntimeException (sprintf('Container "%s" doesn\'t exist', $container));
		}

        // put blob in the storage
		$this->client->putBlob($container, $this->app_file, $temp_file);

		// no need for this anymore
		unlink($temp_file);
		
		// get package location
		$app_instance = $this->client->getBlobInstance($container, $this->app_file);
		
		return $app_instance->Url;
    }
}
