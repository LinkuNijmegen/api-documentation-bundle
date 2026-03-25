<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Tests\DependencyInjection;

use Linku\ApiDocumentationBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function test_it_applies_defaults(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), []);

        self::assertSame(['default' => ['prefix' => '', 'title' => 'API']], $config['sections']);
        self::assertSame('default', $config['default_section']);
        self::assertSame(
            [
                'parameters' => [],
                'request_bodies' => [],
                'responses' => [],
            ],
            $config['removal'],
        );
    }

    public function test_it_processes_custom_configuration(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [[
            'sections' => [
                'admin' => [
                    'prefix' => 'admin',
                    'title' => 'Admin',
                ],
            ],
            'default_section' => 'admin',
            'removal' => [
                'parameters' => [[
                    'path' => '/users',
                    'method' => 'GET',
                    'name' => 'page',
                ]],
                'request_bodies' => [[
                    'path' => '/users',
                    'method' => 'POST',
                ]],
                'responses' => [[
                    'path' => '/users',
                    'method' => 'GET',
                    'statusCode' => '200',
                ]],
            ],
        ]]);

        self::assertSame(
            ['admin' => ['prefix' => 'admin', 'title' => 'Admin']],
            $config['sections'],
        );
        self::assertSame('admin', $config['default_section']);
        self::assertSame(
            [[
                'path' => '/users',
                'method' => 'GET',
                'name' => 'page',
            ]],
            $config['removal']['parameters'],
        );
        self::assertSame(
            [[
                'path' => '/users',
                'method' => 'POST',
            ]],
            $config['removal']['request_bodies'],
        );
        self::assertSame(
            [[
                'path' => '/users',
                'method' => 'GET',
                'statusCode' => '200',
            ]],
            $config['removal']['responses'],
        );
    }
}
