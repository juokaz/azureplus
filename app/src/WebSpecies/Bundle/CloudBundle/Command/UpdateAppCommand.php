<?php

namespace WebSpecies\Bundle\CloudBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateAppCommand extends ContainerAwareCommand
{
    /**
     * @var \WebSpecies\Bundle\CloudBundle\Service\Manager
     */
    private $client;

    protected function configure()
    {
        $this
            ->setName('cloud:app:update')
            ->setDescription('Update app base instance')
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

        $app = $this->client->updateApp($app);

        $output->writeln(sprintf('<info>App updated "%s"</info>', $app->getName()));
    }
}
