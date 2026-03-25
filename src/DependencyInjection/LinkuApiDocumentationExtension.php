<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\DependencyInjection;

use Linku\ApiDocumentationBundle\Builder\OpenApiBuilder;
use Linku\ApiDocumentationBundle\Extensions\OpenApiExtender;
use Linku\ApiDocumentationBundle\Extensions\OpenApiExtension;
use Linku\ApiDocumentationBundle\Removal\RemoveParameters;
use Linku\ApiDocumentationBundle\Removal\RemoveRequestBodies;
use Linku\ApiDocumentationBundle\Removal\RemoveResponses;
use Linku\ApiDocumentationBundle\Sections\DocumentationFilter;
use Linku\ApiDocumentationBundle\Sections\ResourceMetadataCollectionFactory;
use Linku\ApiDocumentationBundle\Sections\Sections;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension as BaseExtension;
use Symfony\Component\DependencyInjection\Reference;

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

        $this->loadServiceDefinitions($container);
    }

    private function loadServiceDefinitions(ContainerBuilder $container): void
    {
        $container->setDefinition(
            'linku_api_documentation.builder.open_api_builder',
            new Definition(OpenApiBuilder::class),
        );
        $container->setAlias(
            OpenApiBuilder::class,
            new Alias('linku_api_documentation.builder.open_api_builder'),
        );

        $container->setDefinition(
            'linku_api_documentation.extensions.open_api_extender',
            (new Definition(OpenApiExtender::class))
                ->setDecoratedService('api_platform.openapi.factory', null, -10)
                ->setArguments([
                    '$decorated' => new Reference('linku_api_documentation.extensions.open_api_extender.inner'),
                    '$sections' => new Reference('linku_api_documentation.sections.sections'),
                    '$extensions' => new TaggedIteratorArgument('linku_api_documentation.extensions.extension'),
                ]),
        );

        $container->setDefinition(
            'linku_api_documentation.sections.sections',
            (new Definition(Sections::class))
                ->setArguments([
                    new Reference('request_stack'),
                    new Reference('router'),
                    '%linku_api_documentation.sections%',
                    '%linku_api_documentation.default_section%',
                ]),
        );
        $container->setAlias(
            Sections::class,
            new Alias('linku_api_documentation.sections.sections'),
        );

        $container->setDefinition(
            'linku_api_documentation.sections.resource_metadata_collection_factory',
            (new Definition(ResourceMetadataCollectionFactory::class))
                ->setDecoratedService('api_platform.metadata.resource.metadata_collection_factory', null, -15)
                ->setArguments([
                    new Reference('linku_api_documentation.sections.resource_metadata_collection_factory.inner'),
                    new Reference('linku_api_documentation.sections.sections'),
                ]),
        );

        $container->setDefinition(
            'linku_api_documentation.sections.documentation_filter',
            (new Definition(DocumentationFilter::class))
                ->setDecoratedService('api_platform.openapi.factory', null, -100)
                ->setArguments([
                    new Reference('linku_api_documentation.sections.documentation_filter.inner'),
                    new Reference('linku_api_documentation.sections.resource_metadata_collection_factory'),
                    new Reference('linku_api_documentation.sections.sections'),
                ]),
        );

        $container->setDefinition(
            'linku_api_documentation.removal.remove_parameters',
            (new Definition(RemoveParameters::class))
                ->setArguments([
                    '$builder' => new Reference('linku_api_documentation.builder.open_api_builder'),
                    '$parameters' => '%linku_api_documentation.removal.parameters%',
                ])
                ->addTag('linku_api_documentation.extensions.extension'),
        );

        $container->setDefinition(
            'linku_api_documentation.removal.remove_request_bodies',
            (new Definition(RemoveRequestBodies::class))
                ->setArguments([
                    '$builder' => new Reference('linku_api_documentation.builder.open_api_builder'),
                    '$requestBodies' => '%linku_api_documentation.removal.request_bodies%',
                ])
                ->addTag('linku_api_documentation.extensions.extension'),
        );

        $container->setDefinition(
            'linku_api_documentation.removal.remove_responses',
            (new Definition(RemoveResponses::class))
                ->setArguments([
                    '$builder' => new Reference('linku_api_documentation.builder.open_api_builder'),
                    '$responses' => '%linku_api_documentation.removal.responses%',
                ])
                ->addTag('linku_api_documentation.extensions.extension'),
        );
    }
}
