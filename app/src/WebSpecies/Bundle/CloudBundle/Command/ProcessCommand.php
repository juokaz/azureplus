<?php

namespace WebSpecies\Bundle\CloudBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
    
use WebSpecies\Bundle\CloudBundle\Entity\App;
use WebSpecies\Bundle\CloudBundle\Entity\Log;

class ProcessCommand extends ContainerAwareCommand
{
    /**
     * @var \WebSpecies\Bundle\CloudBundle\Service\Apps
     */
    private $client;

    /**
     * @var \WebSpecies\Bundle\CloudBundle\Service\Deploy
     */
    private $deploy;

    protected function configure()
    {
        $this
            ->setName('cloud:process')
            ->setDescription('Process requests')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        
        $this->client = $this->getContainer()->get('cloud.service.apps');
        $this->deploy = $this->getContainer()->get('cloud.service.deploy');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $em \Doctrine\Orm\EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        /** @var $apps \WebSpecies\Bundle\CloudBundle\Entity\AppManager */
        $apps = $this->getContainer()->get('cloud.manager.app');

        foreach ($apps->getAppsToSetUp() as $app) {
            $output->writeln(sprintf('<comment>Setting up the app "%s"</comment>', $app->getName()));
            $this->client->setupApp($app);
            $output->writeln(sprintf('<info>Set up the app "%s"</info>', $app->getName()));
        }

        // flush apps in process of deploying
        $em->flush();

        foreach ($apps->getAppsToFinish() as $app) {
            $output->writeln(sprintf('<comment>Checking if app "%s" is deployed</comment>', $app->getName()));
            if ($this->client->isDeployed($app)) {
                // set status
                $app->setStatus(App::STATUS_LIVE);
                // add log
                $app->addLogMessage('Live!', Log::CAT_SETUP);
                $output->writeln(sprintf('<info>App "%s" is deployed</info>', $app->getName()));
            }
        }

        // flush apps to be deployed
        $em->flush();

        foreach ($apps->getAppsToBeDeleted() as $app) {
            $output->writeln(sprintf('<comment>Deleting the app "%s"</comment>', $app->getName()));
            $this->deploy->delete($app);
            $output->writeln(sprintf('<comment>Deleted deployment folder of the app "%s"</comment>', $app->getName()));
            $this->client->deleteApp($app);
            $output->writeln(sprintf('<info>Deleted the app "%s"</info>', $app->getName()));
            $em->remove($app);
        }

        // delete apps to be deleted
        $em->flush();

        foreach ($apps->getAppsWithGitPath() as $app) {
            if ($this->deploy->deployCheckout($app)) {
                $output->writeln(sprintf('<info>Updated the app "%s"</info>', $app->getName()));
            }
        }

        // save log messages
        $em->flush();
    }
}
