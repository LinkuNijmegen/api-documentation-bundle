<?php

declare(strict_types=1);

use Linku\ApiDocumentationBundle\Sections\DocumentationFilter;
use Linku\ApiDocumentationBundle\Sections\ResourceMetadataCollectionFactory;
use Linku\ApiDocumentationBundle\Sections\Sections;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('linku_api_documentation.sections.sections', Sections::class)
        ->args([
            service('request_stack'),
            service('router'),
            param('linku_api_documentation.sections'),
            param('linku_api_documentation.default_section'),
        ]);
    $services->alias(Sections::class, 'linku_api_documentation.sections.sections');

    $services
        ->set(
            'linku_api_documentation.sections.resource_metadata_collection_factory',
            ResourceMetadataCollectionFactory::class,
        )
        ->decorate('api_platform.metadata.resource.metadata_collection_factory', null, -15)
        ->args([
            service('linku_api_documentation.sections.resource_metadata_collection_factory.inner'),
            service('linku_api_documentation.sections.sections'),
        ]);

    $services
        ->set('linku_api_documentation.sections.documentation_filter', DocumentationFilter::class)
        ->decorate('api_platform.openapi.factory', null, -100)
        ->args([
            service('linku_api_documentation.sections.documentation_filter.inner'),
            service('linku_api_documentation.sections.resource_metadata_collection_factory'),
            service('linku_api_documentation.sections.sections'),
        ]);
};
