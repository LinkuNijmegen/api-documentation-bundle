<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Tests\Extensions;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Info;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\OpenApi;
use Linku\ApiDocumentationBundle\Extensions\OpenApiExtender;
use Linku\ApiDocumentationBundle\Extensions\OpenApiExtension;
use Linku\ApiDocumentationBundle\Sections\Sections;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class OpenApiExtenderTest extends TestCase
{
    public function testItIgnoresNonExtensionServicesInTheExtensionIterable(): void
    {
        $decorated = new class() implements OpenApiFactoryInterface {
            public function __invoke(array $context = []): OpenApi
            {
                return new OpenApi(new Info('Original', '1.0.0'), [], new Paths());
            }
        };

        $sections = new Sections(
            new RequestStack(),
            $this->createStub(UrlGeneratorInterface::class),
            [
                'default' => [
                    'prefix' => '',
                    'title' => 'Default',
                ],
            ]
        );

        $validExtension = new class() implements OpenApiExtension {
            public function __invoke(OpenApi $docs): OpenApi
            {
                return $docs->withInfo($docs->getInfo()->withTitle('Extended'));
            }
        };

        $invalidExtension = new \stdClass();

        $extensions = [$invalidExtension, $validExtension];

        $extender = new OpenApiExtender(
            $decorated,
            $sections,
            $extensions,
        );

        $docs = $extender();

        self::assertSame('Extended', $docs->getInfo()->getTitle());
    }
}
