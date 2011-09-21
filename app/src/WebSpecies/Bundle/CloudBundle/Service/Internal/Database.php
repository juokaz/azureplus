<?php

namespace WebSpecies\Bundle\CloudBundle\Service\Internal;

class Database
{
    /**
     * @var \Microsoft_SqlAzure_Management_Client
     */
    private $client;
    
    public function __construct(\Microsoft_SqlAzure_Management_Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create new database server
     *
     * @param string $admin
     * @param string $password
     * @param string $location
     * @return string
     */
    public function createServer($admin, $password, $location)
    {
        $result = $this->client->createServer($admin, $password, $location);

        // allow this server to be accessed from Azure instances
        $this->client->createFirewallRuleForMicrosoftServices($result->Name, true);

        return $result->Name;
    }

    /**
     * Drop database server
     *
     * @param string $name
     * @return void
     */
    public function dropServer($name)
    {
        $this->client->dropServer($name);
    }
}