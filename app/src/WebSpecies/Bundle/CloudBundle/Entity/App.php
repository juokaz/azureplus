<?php

namespace WebSpecies\Bundle\CloudBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="apps")
 */
class App
{
    const STATUS_NEW = 'new';
    const STATUS_DEPLOYED = 'deployed';

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $storage_identifier;

    /**
     * @ORM\ManyToOne(targetEntity="WebSpecies\Bundle\CloudBundle\Entity\User", inversedBy="apps")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $status;
    
    public function __construct(User $user, $name)
    {
        $this->user = $user;
        $this->name = $name;
        $this->status = self::STATUS_NEW;
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function setStorageIdentifier($identifier)
    {
        $this->storage_identifier = $identifier;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }
    
    public function getContainer()
    {
        return $this->getName();
    }
}
