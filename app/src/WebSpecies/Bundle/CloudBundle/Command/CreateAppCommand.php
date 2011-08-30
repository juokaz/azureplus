<?php

namespace WebSpecies\Bundle\CloudBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateAppCommand extends ContainerAwareCommand
{
    private $client;

    protected function configure()
    {
        $this
            ->setName('cloud:app:create')
            ->setDescription('Creates new app in the cloud')
            ->addArgument('user', InputArgument::REQUIRED, 'User name')
            ->addArgument('app', InputArgument::REQUIRED, 'APP name')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        
        $this->client = $this->getContainer()->get('cloud.service.apps');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $input->getArgument('app');
        $user = $input->getArgument('user');

        $user = $this->getContainer()->get('cloud.manager.user')->getUser($user);

        if (!$user) {
            $output->writeln('<error>User not found</error>');
            return;
        }
        
        $app = $this->getContainer()->get('cloud.manager.app')->createApp($user, $app);

        try {
            if (!$this->client->createApp($app)) {
                // @todo handle the failure here
                return;
            }
        } catch (\InvalidArgumentException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return;
        }

        // Save app instance
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->persist($app);
        $em->flush();

        $output->writeln(sprintf('<info>Created the app: %s</info>', $app->getUrl()));
    }
}
