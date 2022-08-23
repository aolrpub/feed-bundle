<?php
namespace Aolr\FeedBundle\DependencyInjection;

use Aolr\FeedBundle\Service\FeedManager;
use Aolr\FeedBundle\DependencyInjection\Configuration;
use MongoDB\Client;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class AolrFeedExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $yamlLoader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $yamlLoader->load('service.yml');

        $mongoClient = $container->getDefinition(Client::class);
        $mongoClient->replaceArgument(0, $config['mongodb_url']);

        $feedService = $container->getDefinition(FeedManager::class);
        $feedService->addMethodCall('setEventPath', [$config['event_path']]);
        if (!empty($config['database']) && !empty($config['table'])) {
            $feedService->addMethodCall('setCollection', [$config['database'], $config['table']]);
        }
    }
}
