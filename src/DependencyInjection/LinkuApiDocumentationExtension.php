<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\DependencyInjection;

use Linku\ApiDocumentationBundle\Extensions\OpenApiExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension as BaseExtension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class LinkuApiDocumentationExtension extends BaseExtension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(OpenApiExtension::class)
            ->addTag('linku_api_documentation.extensions.extension');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('linku_api_documentation.sections', $config['sections']);
        $container->setParameter('linku_api_documentation.default_section', $config['default_section']);
        $container->setParameter('linku_api_documentation.removal.parameters', $config['removal']['parameters']);
        $container->setParameter('linku_api_documentation.removal.request_bodies', $config['removal']['request_bodies']);
        $container->setParameter('linku_api_documentation.removal.responses', $config['removal']['responses']);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        foreach (['builder.php', 'extensions.php', 'removal.php', 'sections.php'] as $file) {
            $loader->load($file);
        }
    }
}
