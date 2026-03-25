<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('linku_api_documentation');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('sections')
                    ->defaultValue(['default' => ['prefix' => '', 'title' => 'API']])
                    ->prototype('array')
                        ->append(new ScalarNodeDefinition('prefix'))
                        ->append(new ScalarNodeDefinition('title'))
                    ->end()
                ->end()
                ->scalarNode('default_section')
                    ->defaultValue('default')
                ->end()
                ->arrayNode('removal')
                    ->addDefaultsIfNotSet()
                    ->children()
                    ->arrayNode('parameters')
                        ->defaultValue([])
                        ->prototype('array')
                            ->append(new ScalarNodeDefinition('path'))
                            ->append(new ScalarNodeDefinition('method'))
                            ->append(new ScalarNodeDefinition('name'))
                        ->end()
                    ->end()
                    ->arrayNode('request_bodies')
                        ->defaultValue([])
                        ->prototype('array')
                            ->append(new ScalarNodeDefinition('path'))
                            ->append(new ScalarNodeDefinition('method'))
                        ->end()
                    ->end()
                    ->arrayNode('responses')
                        ->defaultValue([])
                        ->prototype('array')
                            ->append(new ScalarNodeDefinition('path'))
                            ->append(new ScalarNodeDefinition('method'))
                            ->append(new ScalarNodeDefinition('statusCode'))
                        ->end()
                    ->end()
                ->end()
        ;

        return $treeBuilder;
    }
}
