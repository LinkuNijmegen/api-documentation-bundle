<?php

declare(strict_types=1);

use Linku\ApiDocumentationBundle\Extensions\OpenApiExtender;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('linku_api_documentation.extensions.open_api_extender', OpenApiExtender::class)
        ->decorate('api_platform.openapi.factory', null, -10)
        ->arg('$decorated', service('linku_api_documentation.extensions.open_api_extender.inner'))
        ->arg('$sections', service('linku_api_documentation.sections.sections'))
        ->arg('$extensions', tagged_iterator('linku_api_documentation.extensions.extension'));
};
