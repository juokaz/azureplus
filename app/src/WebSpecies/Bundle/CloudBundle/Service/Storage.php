<?php

namespace WebSpecies\Bundle\CloudBundle\Service;

class Storage
{
    private $client;
    
    public function __construct(\Microsoft_WindowsAzure_Storage_Blob $client)
    {
        $this->client = $client;
    }
    
    public function store($container, $name, $content)
    {
        // check if container is in place
		if (!$this->client->containerExists($container)) {
			throw new \RuntimeException (sprintf('Container "%s" doesn\'t exist', $container));
		}

        // put blob in the storage
		$this->client->putBlob($container, $name, $content);

		// get package location
		$app_instance = $this->client->getBlobInstance($container, $name);
		
		return $app_instance->Url;
    }
}
