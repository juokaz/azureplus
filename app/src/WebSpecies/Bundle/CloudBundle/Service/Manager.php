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
    private $app_manager;
    private $user_manager;
    
    public function __construct(Apps $apps, AppManager $app_manager, UserManager $user_manager)
    {
        $this->apps = $apps;
        $this->app_manager = $app_manager;
        $this->user_manager = $user_manager;
    }

    /**
     * Create APP
     *
     * @param string|\WebSpecies\Bundle\CloudBundle\Entity\App $name
     * @param string|\WebSpecies\Bundle\CloudBundle\Entity\User $username
     * @return \WebSpecies\Bundle\CloudBundle\Entity\App
     */
    public function createApp($name, $username = null)
    {
        if ($username) {
            if ($username instanceof User) {
                $user = $username;
            } else {
                $user = $this->user_manager->getUser($username);
            }
        }

        // Create app entity
        /** @var $app \WebSpecies\Bundle\CloudBundle\Entity\App */
        if ($name instanceof App) {
            $app = $name;
        } else {
            if (!isset($user) || !$user) {
                throw new \InvalidArgumentException('User not found');
            }

            $app = $this->app_manager->createApp($user, $name);
        }

        // Create app instance
        $this->apps->createApp($app);

        // Save app instance
        $this->app_manager->saveApp($app);

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
        
        $this->app_manager->saveApp($app);

        return $app;
    }

    /**
     * Update APP
     *
     * @param string $name
     * @return \WebSpecies\Bundle\CloudBundle\Entity\App
     */
    public function updateApp($name)
    {
        if ($name instanceof App) {
            $app = $name;
        } else {
            $app = $this->app_manager->getApp($name);
        }

        if (!$app) {
            throw new \InvalidArgumentException('App not found');
        }

        $app->setStatus(App::STATUS_CREATING);

        $this->app_manager->saveApp($app);

        return $app;
    }
}