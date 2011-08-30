<?php

namespace WebSpecies\Bundle\CloudBundle\Document;

use Doctrine\ORM\EntityManager;

class AppManager
{
    private $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getApp($app)
    {
        return new App($app);
    }
}
