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
            ->end()
        ;

        return $treeBuilder;
    }
}
