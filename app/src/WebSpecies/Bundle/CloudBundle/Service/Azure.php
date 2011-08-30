<?php

namespace WebSpecies\Bundle\CloudBundle\Service;

use WebSpecies\Bundle\CloudBundle\Entity\App;

class Azure
{
    /**
     * @var \Microsoft_WindowsAzure_Management_Client
     */
    private $client;
    
    public function __construct(\Microsoft_WindowsAzure_Management_Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create server
     *
     * @throws \RuntimeException
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @param string $region
     * @return bool
     */
    public function createServer(App $app, $region = 'West Europe')
    {
        $name = $app->getName();
        
		try {
			$this->client->createHostedService($name, $name, null, $region);
		} catch (\Exception $e) {
            if ($e->getMessage() != 'The specified DNS name is already taken.') {
			    throw new \RuntimeException('Error creating hosted service', null, $e);
            } else {
                throw new \InvalidArgumentException('DNS name is already taken', null, $e);
            }
		}

        return true;
    }

    /**
     * Create deployment
     *
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @param string $package
     * @param string $configuration
     * @return bool
     */
    public function createDeployment(App $app, $package, $configuration)
    {
        $this->client->createDeployment($app->getName(), 'production', 'deployment', 'deployment', $package, $configuration, true);

        return true;
    }

    /**
     * Get service URL
     *
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @param string $where
     * @return string
     */
    public function getUrl(App $app, $where = 'production')
    {
        $deployment = $this->client->getDeploymentBySlot($app->getName(), $where);

        return $deployment->url;
    }

    /**
     * Get app status
     *
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @return string
     */
    public function getStatus(App $app)
    {
        $deployment = $this->client->getDeploymentBySlot($app->getName(), 'production');

        if ($deployment->status != 'Running') {
			return $deployment->status;
		}

        $instance = current($deployment->roleinstancelist);

		return $instance['instancestatus'];
    }
}