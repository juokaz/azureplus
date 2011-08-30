<?php

namespace WebSpecies\Bundle\CloudBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cloud');

        $rootNode
            ->children()
                ->scalarNode('storage_account')->end()
                ->scalarNode('storage_key')->end()

                ->scalarNode('azure_subscription')->end()
                ->scalarNode('azure_password')->end()
                ->scalarNode('azure_certificate')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
