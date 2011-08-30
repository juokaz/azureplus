<?php

namespace WebSpecies\Bundle\CloudBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeployCommand extends ContainerAwareCommand
{
    private $client;

    protected function configure()
    {
        $this
            ->setName('cloud:deploy')
            ->setDescription('Deploys app to the cloud')
            ->addArgument('app', InputArgument::REQUIRED, 'APP name')
            ->addArgument('location', InputArgument::REQUIRED, 'Where app is stored at')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        
        $this->client = $this->getContainer()->get('cloud.service.deploy');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $input->getArgument('app');
        $folder = $input->getArgument('location');
        
        $app = $this->getContainer()->get('cloud.manager.app')->getApp($app);    
    
        if ($result = $this->client->deploy($app, $folder)) {
            $output->writeln(sprintf('<info>Deployed app package to "%s"</info>', $result));
        }
    }
}
