<?php

namespace WebSpecies\Bundle\CloudBundle\Entity;

use Doctrine\ORM\EntityManager;

class UserManager
{
    private $em;
    private $class = 'WebSpecies\Bundle\CloudBundle\Entity\User';
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get user by name
     *
     * @param string $name
     * @return \WebSpecies\Bundle\CloudBundle\Entity\User
     */
    public function getUser($name)
    {
        return $this->em->getRepository($this->class)->findOneBy(array('username' => $name));
    }
}
