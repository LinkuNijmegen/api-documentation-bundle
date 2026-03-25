<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Tests\Sections;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Operation as GraphQlOperation;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operations;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use Linku\ApiDocumentationBundle\Sections\ResourceMetadataCollectionFactory;
use Linku\ApiDocumentationBundle\Sections\Sections;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ResourceMetadataCollectionFactoryTest extends TestCase
{
    public function testItKeepsNonHttpOperationsWhenFilteringMixedOperations(): void
    {
        $resource = new ApiResource(operations: [
            'get_tasks' => new HttpOperation(uriTemplate: '/v1/tasks'),
        ]);

        $operationsProperty = new \ReflectionProperty(ApiResource::class, 'operations');
        $operationsProperty->setValue($resource, new Operations([
            'get_tasks' => new HttpOperation(uriTemplate: '/v1/tasks'),
            'task_query' => new GraphQlOperation(name: 'taskQuery'),
        ]));

        $decorated = new class($resource) implements ResourceMetadataCollectionFactoryInterface {
            public function __construct(
                private readonly ApiResource $resource,
            ) {
            }

            public function create(string $resourceClass): ResourceMetadataCollection
            {
                return new ResourceMetadataCollection($resourceClass, [$this->resource]);
            }
        };

        $sections = new Sections(
            new RequestStack(),
            $this->createStub(UrlGeneratorInterface::class),
            [
                'api' => [
                    'prefix' => 'v1',
                    'title' => 'API',
                ],
            ]
        );

        $factory = new ResourceMetadataCollectionFactory($decorated, $sections);
        $factory->enable();

        $metadataCollection = $factory->create('App\\Entity\\Task');
        $metadata = $metadataCollection[0] ?? null;

        self::assertInstanceOf(ApiResource::class, $metadata);

        $filteredOperations = $metadata->getOperations();
        self::assertNotNull($filteredOperations);
        self::assertCount(2, $filteredOperations);
        self::assertTrue($filteredOperations->has('get_tasks'));
        self::assertTrue($filteredOperations->has('taskQuery'));
    }
}
