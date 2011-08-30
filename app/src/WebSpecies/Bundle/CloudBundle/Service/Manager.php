<?php

namespace WebSpecies\Bundle\CloudBundle\Service;

use Doctrine\ORM\EntityManager;
use WebSpecies\Bundle\CloudBundle\Entity\App;
use WebSpecies\Bundle\CloudBundle\Entity\AppManager;
use WebSpecies\Bundle\CloudBundle\Entity\User;
use WebSpecies\Bundle\CloudBundle\Entity\UserManager;

class Manager
{
    private $apps;
    private $storage;
    private $em;
    private $app_manager;
    private $user_manager;
    
    public function __construct(EntityManager $em, Storage $storage, Apps $apps, AppManager $app_manager, UserManager $user_manager)
    {
        $this->storage = $storage;
        $this->apps = $apps;
        $this->em = $em;
        $this->app_manager = $app_manager;
        $this->user_manager = $user_manager;
    }

    /**
     * Create APP
     *
     * @param string $username
     * @param string $name
     * @return \WebSpecies\Bundle\CloudBundle\Entity\App
     */
    public function createApp($username, $name)
    {
        if ($username instanceof User) {
            $user = $username;
        } else {
            $user = $this->user_manager->getUser($username);
        }

        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $app = $this->app_manager->createApp($user, $name);

        // Save app instance
        $this->em->persist($app);
        $this->em->flush();

        return $app;
    }

    /**
     * Delete APP
     *
     * @param string $name
     * @return \WebSpecies\Bundle\CloudBundle\Entity\App
     */
    public function deleteApp($name)
    {
        if ($name instanceof App) {
            $app = $name;
        } else {
            $app = $this->app_manager->getApp($name);
        }

        if (!$app) {
            throw new \InvalidArgumentException('App not found');
        }

        $app->setStatus(App::STATUS_DELETED);
        
        $this->em->flush();

        return $app;
    }
}