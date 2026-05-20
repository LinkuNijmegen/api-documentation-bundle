<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('_linku_api_documentation_section_docs', '/{section}/docs.{_format}')
        ->controller('api_platform.action.documentation')
        ->defaults([
            '_format' => '',
            '_api_respond' => true,
        ]);
};
