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
     * Save app
     *
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @return void
     */
    public function saveApp(App $app)
    {
        if ($this->em->contains($app) && $app->getConfiguration()->getChanges()) {
            $app->addLogMessage($this->getChangesMessage($app), Log::CAT_GENERAL);
        }

        $this->em->persist($app);
        $this->em->flush();
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
                                WHERE s.git_repository IS NOT NULL AND s.git_repository != \'\' AND a.status = :status');
        
        // only live apps need deployments
        $query->setParameter('status', App::STATUS_LIVE);

        return $query->getResult();
    }

    /**
     * Get changes message
     *
     * @throws \InvalidArgumentException
     * @param App $app
     * @return string
     */
    private function getChangesMessage(App $app)
    {
        $changes = $app->getConfiguration()->getChanges();
        $messages = array();

        foreach ($changes as $change) {
            switch ($change) {
                case 'php_version':
                    $messages[] = sprintf('PHP version to "%s"', $app->getConfiguration()->getPHPVersion());
                    break;
                case 'app_root':
                    $messages[] = sprintf('App root to "%s"', $app->getConfiguration()->getAppRoot());
                    break;
                case 'mode':
                    $messages[] = sprintf('Mode to "%s"', $app->getConfiguration()->getProduction() ? 'production' : 'development');
                    break;
                default:
                    throw new \InvalidArgumentException('Change type ' . $change . ' unrecognized');
                    break;
            }
        }

        return sprintf('Changed %s.', implode(', ', $messages));
    }
}
