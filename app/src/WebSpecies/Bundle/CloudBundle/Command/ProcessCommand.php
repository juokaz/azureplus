<?php

namespace WebSpecies\Bundle\CloudBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
    
use WebSpecies\Bundle\CloudBundle\Entity\App;

class ProcessCommand extends ContainerAwareCommand
{
    /**
     * @var \WebSpecies\Bundle\CloudBundle\Service\Apps
     */
    private $client;

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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $em \Doctrine\Orm\EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        /** @var $apps \WebSpecies\Bundle\CloudBundle\Entity\AppManager */
        $apps = $this->getContainer()->get('cloud.manager.app');

        foreach ($apps->getAppsToCreate() as $app) {
            $output->writeln(sprintf('<comment>Creating the app "%s"</comment>', $app->getName()));
            $this->client->createApp($app);
            $output->writeln(sprintf('<info>Created the app "%s"</info>', $app->getName()));
        }

        // flush apps in process of deploying
        $em->flush();

        foreach ($apps->getAppsToFinish() as $app) {
            $output->writeln(sprintf('<comment>Checking if app "%s" is deployed</comment>', $app->getName()));
            if ($this->client->isDeployed($app)) {
                $app->setStatus(App::STATUS_DEPLOYED);
                $output->writeln(sprintf('<info>App "%s" is deployed</info>', $app->getName()));
            }
        }

        // flush apps to be deployed
        $em->flush();

        foreach ($apps->getAppsToBeDeleted() as $app) {
            $output->writeln(sprintf('<comment>Deleting the app "%s"</comment>', $app->getName()));
            $this->client->deleteApp($app);
            $output->writeln(sprintf('<info>Deleted the app "%s"</info>', $app->getName()));
            $em->remove($app);
        }

        // delete apps to be deleted
        $em->flush();
    }
}
