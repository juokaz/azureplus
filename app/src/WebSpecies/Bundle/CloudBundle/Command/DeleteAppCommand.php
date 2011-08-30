<?php

namespace WebSpecies\Bundle\CloudBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteAppCommand extends ContainerAwareCommand
{
    private $client;

    protected function configure()
    {
        $this
            ->setName('cloud:app:delete')
            ->setDescription('Delete app')
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
        
        $app = $this->getContainer()->get('cloud.manager.app')->getApp($app);    

        $this->client->deleteApp($app);

        $output->writeln(sprintf('<info>App deleted: %s</info>', $app->getUrl()));
    }
}
