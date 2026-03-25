<?php

declare(strict_types=1);

use Linku\ApiDocumentationBundle\Removal\RemoveParameters;
use Linku\ApiDocumentationBundle\Removal\RemoveRequestBodies;
use Linku\ApiDocumentationBundle\Removal\RemoveResponses;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('linku_api_documentation.removal.remove_parameters', RemoveParameters::class)
        ->arg('$builder', service('linku_api_documentation.builder.open_api_builder'))
        ->arg('$parameters', param('linku_api_documentation.removal.parameters'))
        ->tag('linku_api_documentation.extensions.extension');

    $services
        ->set('linku_api_documentation.removal.remove_request_bodies', RemoveRequestBodies::class)
        ->arg('$builder', service('linku_api_documentation.builder.open_api_builder'))
        ->arg('$requestBodies', param('linku_api_documentation.removal.request_bodies'))
        ->tag('linku_api_documentation.extensions.extension');

    $services
        ->set('linku_api_documentation.removal.remove_responses', RemoveResponses::class)
        ->arg('$builder', service('linku_api_documentation.builder.open_api_builder'))
        ->arg('$responses', param('linku_api_documentation.removal.responses'))
        ->tag('linku_api_documentation.extensions.extension');
};
