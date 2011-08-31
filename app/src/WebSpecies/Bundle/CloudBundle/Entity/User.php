<?php

namespace WebSpecies\Bundle\CloudBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="WebSpecies\Bundle\CloudBundle\Entity\App", mappedBy="user")
     */
    protected $apps;

    public function __construct()
    {
        parent::__construct();

        $this->apps = new ArrayCollection();
    }
}