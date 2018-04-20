<?php

namespace Simettric\DoctrineTranslatableFormBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('simettric_doctrine_translatable_form');

        $rootNode
            ->children()
                ->arrayNode('locales')
                    ->prototype('scalar')->defaultValue('en')->end()
                ->end()
            ->end()
            ->children()
                ->scalarNode('required_locale')->defaultValue('en')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
