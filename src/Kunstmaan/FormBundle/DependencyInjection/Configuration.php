<?php

namespace Kunstmaan\FormBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('kunstmaan_form');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('deletable_formsubmissions')->defaultFalse()->end()
                ->scalarNode('web_root')
                    ->defaultValue('%kernel.project_dir%/public')
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
