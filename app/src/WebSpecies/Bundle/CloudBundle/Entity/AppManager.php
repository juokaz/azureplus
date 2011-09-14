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
     * @param string|null $name
     * @return \WebSpecies\Bundle\CloudBundle\Entity\App
     */
    public function createApp(User $user, $name = null)
    {
        return new $this->class($user, $name);
    }

    /**
     * List of apps belonging to the user
     *
     * @return \WebSpecies\Bundle\CloudBundle\Entity\App[]
     */
    public function getUserApps(User $user)
    {
        $query = $this->em->createQuery('SELECT a FROM ' . $this->class . ' a WHERE a.user = ?1 AND a.status != \'' . APP::STATUS_DELETED . '\'');
        $query->setParameter(1, $user->getId());

        return $query->getResult();        
    }

    /**
     * List of apps to set up in azure servers
     *
     * @return \WebSpecies\Bundle\CloudBundle\Entity\App[]
     */
    public function getAppsToSetUp()
    {
        return $this->em->getRepository($this->class)->findBy(array('status' => App::STATUS_CREATING));
    }

    /**
     * List of apps which might have been finished to deploy
     *
     * @return \WebSpecies\Bundle\CloudBundle\Entity\App[]
     */
    public function getAppsToFinish()
    {
        return $this->em->getRepository($this->class)->findBy(array('status' => App::STATUS_CREATED));
    }

    /**
     * List of apps which need to be deleted
     *
     * @return \WebSpecies\Bundle\CloudBundle\Entity\App[]
     */
    public function getAppsToBeDeleted()
    {
        return $this->em->getRepository($this->class)->findBy(array('status' => App::STATUS_DELETED));
    }

    /**
     * List of apps with git path
     *
     * @return \WebSpecies\Bundle\CloudBundle\Entity\App[]
     */
    public function getAppsWithGitPath()
    {
        $query = $this->em->createQuery('SELECT a FROM ' . $this->class . ' a JOIN a.source s
                                WHERE s.git_repository IS NOT NULL AND s.git_repository != \'\'');

        return $query->getResult();
    }
}
