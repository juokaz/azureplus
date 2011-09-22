<?php

namespace WebSpecies\Bundle\CloudBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="dbs")
 */
class Database
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $server;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="WebSpecies\Bundle\CloudBundle\Entity\App", inversedBy="databases")
     * @ORM\JoinColumn(name="app_id", referencedColumnName="name")
     */
    private $app;

    public function __construct(App $app, $server, $name)
    {
        $this->app = $app;
        $this->server = $server;
        $this->name = $name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return sprintf('%s@%s', $this->user, $this->server);
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setServer($server)
    {
        $this->server = $server;
    }

    public function getServer()
    {
        return sprintf('%s.database.windows.net', $this->server);
    }
}