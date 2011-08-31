<?php

namespace WebSpecies\Bundle\CloudBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateAppCommand extends ContainerAwareCommand
{
    /**
     * @var \WebSpecies\Bundle\CloudBundle\Service\Manager
     */
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
        
        $this->client = $this->getContainer()->get('cloud.service.manager');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $input->getArgument('app');
        $user = $input->getArgument('user');

        try {
            $app = $this->client->createApp($user, $app);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return;
        }

        $output->writeln(sprintf('<info>Created the app "%s"</info>', $app->getName()));
    }
}
