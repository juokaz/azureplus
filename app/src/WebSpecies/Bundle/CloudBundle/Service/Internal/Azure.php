<?php

namespace WebSpecies\Bundle\CloudBundle\Service\Internal;

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
     * @param string $name
     * @param string $region
     * @return bool
     */
    public function createServer($name, $region = 'West Europe')
    {
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
     * @param string $name
     * @param string $package
     * @param string $configuration
     * @return bool
     */
    public function createDeployment($name, $package, $configuration)
    {
        $this->client->createDeployment($name, 'production', 'deployment', 'deployment', $package, $configuration, true);

        return true;
    }

    /**
     * Delete server
     *
     * @param string $name
     * @return bool
     */
    public function deleteServer($name)
    {
		$deployment = false;
		try {
			$this->client->getDeploymentBySlot($name, 'production');
			$deployment = true;
		} catch (\Exception $e) {
			// deployment service might be already deleted or wasn't created at all
            if ($e->getMessage() != 'No deployments were found.') {
                throw $e;
            }
		}

		if ($deployment) {
			$this->client->deleteDeploymentBySlot($name, 'production');
			// Wait for it to finish
			$this->client->waitForOperation();
		}

        try {
		    $this->client->deleteHostedService($name);
        } catch (\Exception $e) {
            // hosted service might be already deleted
            if ($e->getMessage() != 'The hosted service does not exist.') {
                throw $e;
            }
        }

        return true;
    }

    /**
     * Get service URL
     *
     * @param string $name
     * @param string $where
     * @return string
     */
    public function getUrl($name, $where = 'production')
    {
        if ($where == 'production') {
            return sprintf('http://%s.cloudapp.net', $name);
        } else {
            $deployment = $this->client->getDeploymentBySlot($name, $where);

            return $deployment->url;
        }
    }

    /**
     * Is the app deployed
     *
     * @param string $name
     * @param string $where
     * @return bool
     */
    public function isDeployed($name, $where = 'production')
	{
        try {
            $deployment = $this->client->getDeploymentBySlot($name, $where);
        } catch (\Exception $e) {
            // this might fail because of HTTP errors communicating to Azure
            return false;
        }

		if ($deployment->status != 'Running') {
			return false;
		}

		foreach ($deployment->roleinstancelist as $instance) {
			if ($instance['instancestatus'] != 'Ready') {
				return false;
			}
		}

		return true;
	}
}