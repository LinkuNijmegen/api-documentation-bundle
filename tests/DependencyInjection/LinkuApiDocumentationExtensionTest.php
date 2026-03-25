<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Tests\DependencyInjection;

use Linku\ApiDocumentationBundle\DependencyInjection\LinkuApiDocumentationExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class LinkuApiDocumentationExtensionTest extends TestCase
{
    public function testItLoadsServicesAndParameters(): void
    {
        $container = new ContainerBuilder();
        $extension = new LinkuApiDocumentationExtension();

        $extension->load([[
            'sections' => ['admin' => ['prefix' => 'admin', 'title' => 'Admin']],
            'default_section' => 'admin',
            'removal' => ['parameters' => [], 'request_bodies' => [], 'responses' => []],
        ]], $container);

        self::assertTrue($container->hasDefinition('linku_api_documentation.builder.open_api_builder'));
        self::assertTrue($container->hasAlias('Linku\ApiDocumentationBundle\Builder\OpenApiBuilder'));

        self::assertTrue($container->hasDefinition('linku_api_documentation.sections.sections'));
        self::assertTrue($container->hasAlias('Linku\ApiDocumentationBundle\Sections\Sections'));
        $sectionsArguments = $container->getDefinition('linku_api_documentation.sections.sections')->getArguments();
        self::assertSame('request_stack', (string) $sectionsArguments[0]);
        self::assertSame('router', (string) $sectionsArguments[1]);
        self::assertSame('%linku_api_documentation.sections%', $sectionsArguments[2]);
        self::assertSame('%linku_api_documentation.default_section%', $sectionsArguments[3]);

        self::assertDecorates(
            $container->getDefinition('linku_api_documentation.extensions.open_api_extender'),
            'api_platform.openapi.factory',
        );
        self::assertDecorates(
            $container->getDefinition('linku_api_documentation.sections.documentation_filter'),
            'api_platform.openapi.factory',
        );
        self::assertDecorates(
            $container->getDefinition('linku_api_documentation.sections.resource_metadata_collection_factory'),
            'api_platform.metadata.resource.metadata_collection_factory',
        );

        self::assertExtensionTag($container->getDefinition('linku_api_documentation.removal.remove_parameters'));
        self::assertExtensionTag($container->getDefinition('linku_api_documentation.removal.remove_request_bodies'));
        self::assertExtensionTag($container->getDefinition('linku_api_documentation.removal.remove_responses'));

        self::assertSame(
            ['admin' => ['prefix' => 'admin', 'title' => 'Admin']],
            $container->getParameter('linku_api_documentation.sections'),
        );
        self::assertSame('admin', $container->getParameter('linku_api_documentation.default_section'));
        self::assertSame([], $container->getParameter('linku_api_documentation.removal.parameters'));
        self::assertSame([], $container->getParameter('linku_api_documentation.removal.request_bodies'));
        self::assertSame([], $container->getParameter('linku_api_documentation.removal.responses'));
    }

    private static function assertDecorates(Definition $definition, string $decoratedServiceId): void
    {
        self::assertNotNull($definition->getDecoratedService());
        self::assertSame($decoratedServiceId, $definition->getDecoratedService()[0]);
    }

    private static function assertExtensionTag(Definition $definition): void
    {
        self::assertTrue($definition->hasTag('linku_api_documentation.extensions.extension'));
    }
}
