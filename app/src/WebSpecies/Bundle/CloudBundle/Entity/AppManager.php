<?php

namespace WebSpecies\Bundle\CloudBundle\Entity;

use Doctrine\ORM\EntityManager;

class AppManager
{
    private $em;
    private $class = 'WebSpecies\Bundle\CloudBundle\Entity\App';
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get app by name
     *
     * @param string $app
     * @return \WebSpecies\Bundle\CloudBundle\Entity\App
     */
    public function getApp($app)
    {
        return $this->em->find($this->class, $app);
    }

    /**
     * Create new app
     *
     * @param \WebSpecies\Bundle\CloudBundle\Entity\User $user
     * @param string $name
     * @return \WebSpecies\Bundle\CloudBundle\Entity\App
     */
    public function createApp(User $user, $name)
    {
        return new $this->class($user, $name);
    }
}
