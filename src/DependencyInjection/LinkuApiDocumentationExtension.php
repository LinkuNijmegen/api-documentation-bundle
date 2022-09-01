<?php
declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\DependencyInjection;

use Linku\ApiDocumentationBundle\Extensions\OpenApiExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension as BaseExtension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class LinkuApiDocumentationExtension extends BaseExtension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(OpenApiExtension::class)
            ->addTag('linku_api_documentation.extensions.extension');

        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('builder.xml');
        $loader->load('extensions.xml');
        $loader->load('sections.xml');
        $loader->load('removal.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('linku_api_documentation.sections', $config['sections']);
        $container->setParameter('linku_api_documentation.removal.parameters', $config['removal']['parameters']);
        $container->setParameter('linku_api_documentation.removal.request_bodies', $config['removal']['request_bodies']);
        $container->setParameter('linku_api_documentation.removal.responses', $config['removal']['responses']);
    }
}
