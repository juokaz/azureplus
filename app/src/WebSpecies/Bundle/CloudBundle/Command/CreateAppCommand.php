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

        try {
            if (false != ($url = $this->client->createApp($app))) {
                $output->writeln(sprintf('<info>Initialised the app</info>', $url));
            } else {
                // @todo handle the failure here
                return;
            }
        } catch (\InvalidArgumentException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return;
        }

        $output->writeln(sprintf('<info>Created the app: %s</info>', $url));
    }
}
