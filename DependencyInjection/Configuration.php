<?php

namespace EMC\FileinputBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('emc_fileinput');
        
        $rootNode
            ->children()
                ->scalarNode('file_class')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('providers')
                    ->children()
                        ->arrayNode('vimeo')
                            ->children()
                                ->scalarNode('client_id')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('client_secret')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('access_token')->end()
                                ->scalarNode('scope')->cannotBeEmpty()->defaultValue('private interact create edit upload delete public')->end()
                            ->end()
                        ->end()
                    ->end()
            ->end();
                
        return $treeBuilder;
    }
}
