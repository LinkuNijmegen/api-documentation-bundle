<?php

declare(strict_types=1);

use Linku\ApiDocumentationBundle\Builder\OpenApiBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('linku_api_documentation.builder.open_api_builder', OpenApiBuilder::class);
    $services->alias(OpenApiBuilder::class, 'linku_api_documentation.builder.open_api_builder');
};
