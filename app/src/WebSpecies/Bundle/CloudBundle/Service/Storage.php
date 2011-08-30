<?php

namespace WebSpecies\Bundle\CloudBundle\Service;

class Storage
{
    private $client;
    
    public function __construct(\Microsoft_WindowsAzure_Storage_Blob $client)
    {
        $this->client = $client;
    }

    /**
     * Get url
     *
     * @param string $container
     * @param string $name
     * @return string
     */
    public function getUrl($container, $name)
    {
		$app_instance = $this->client->getBlobInstance($container, $name);

		return $app_instance->Url;
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
		return $this->getUrl($container, $name);
    }

    /**
     * Create container
     *
     * @param string $container
     * @return bool
     */
    public function createContainer($container)
    {
		try {
			$this->client->createContainerIfNotExists($container);
		} catch (\Exception $e) {
			throw new \RuntimeException('Failed to create a container', null, $e);
		}

        return true;
    }

    /**
     * Delete container
     *
     * @param string $container
     * @return bool
     */
    public function deleteContainer($container)
    {
        if ($this->client->containerExists($container)) {
            $this->client->deleteContainer($container);
        }

        return true;
    }

    /**
     * Create new identifier for storage
     *
     * @throws \RuntimeException
     * @param string $container
     * @return bool
     */
    public function setIdentifier($container)
    {
        $identifier_ = $this->generateIdentifier();
        $identifier = new \Microsoft_WindowsAzure_Storage_SignedIdentifier($identifier_, $this->isoDate(), $this->isoDate(strtotime('+10 years')), 'r');

		try {
			$this->client->setContainerAcl($container, \Microsoft_WindowsAzure_Storage_Blob::ACL_PRIVATE, array($identifier));
		} catch (\Exception $e) {
			throw new \RuntimeException ('Failed to set ACL', null, $e);
		}

        return $identifier_;
    }

    /**
     * Get signed URL
     *
     * @param string $container
     * @param string $name
     * @param string $identifier
     * @return string
     */
    public function getSignerUrl($container, $name, $identifier)
    {
		$signed = $this->client->generateSharedAccessUrl($container, $name, 'b', '', '', '', $identifier);

		// replace buggy query string params which are not needed
		$signed = str_replace(array('se=', 'sp='), '', $signed);
		$signed = str_replace('&&', '&', $signed);
		$signed = str_replace('?&', '?', $signed);

		return $signed;
    }

    /**
     * Generate an identifier of given length
     *
     * @param int $length
     * @return string
     */
    private function generateIdentifier($length = 64)
    {
        // start with a blank password
        $identifier = "";

        // define possible characters - any character in this string can be
        // picked for use in the password, so if you want to put vowels back in
        // or add special characters such as exclamation marks, this is where
        // you should do it
        $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";

        // we refer to the length of $possible a few times, so let's grab it now
        $maxlength = strlen($possible);

        // check for length overflow and truncate if necessary
        if ($length > $maxlength) {
            $length = $maxlength;
        }

        // set up a counter for how many characters are in the password so far
        $i = 0;

        // add random characters to $password until $length is reached
        while ($i < $length) {
            // pick a random character from the possible ones
            $char = substr($possible, mt_rand(0, $maxlength-1), 1);

            // have we already used this character in $password?
            if (!strstr($identifier, $char)) {
                // no, so it's OK to add it onto the end of whatever we've already got...
                $identifier .= $char;
                // ... and increase the counter by one
                $i++;
            }
        }

        // done!
        return $identifier;
    }

    /**
     * Get date in Azure specific format
     *
     * @param string|null $timestamp
     * @return string
     */
	private function isoDate($timestamp = null) 
	{        
	    $tz = @date_default_timezone_get();
	    @date_default_timezone_set('UTC');

	    if (is_null($timestamp)) {
	        $timestamp = time();
	    }

	    $returnValue = str_replace('+00:00', '.0000000Z', @date('c', $timestamp));
	    @date_default_timezone_set($tz);
	    return $returnValue;
	}
}
