<?php
namespace Aolr\FeedBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('aolr_feed');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('mongodb_url')->cannotBeEmpty()->end()
                ->scalarNode('database')->end()
                ->scalarNode('table')->end()
                ->scalarNode('event_path')->cannotBeEmpty()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
