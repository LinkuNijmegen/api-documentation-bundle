<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Tests\Builder;

use ApiPlatform\OpenApi\Model\Info;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\OpenApi;
use Linku\ApiDocumentationBundle\Builder\OpenApiBuilder;
use PHPUnit\Framework\TestCase;

final class OpenApiBuilderTest extends TestCase
{
    public function testItAddsAPostOperationAndRequestBody(): void
    {
        $builder = new OpenApiBuilder();
        $openApi = new OpenApi(new Info('Demo', '1.0.0'), [], new Paths());

        $openApi = $builder->addPostOperation(
            $openApi,
            '/tasks',
            'postTask',
            ['Tasks'],
            ['201' => new Response('Created')],
            'Create task',
            $builder->createRequestBody('Task payload', 'TaskInput')
        );

        $operation = $openApi->getPaths()->getPath('/tasks')?->getPost();
        $requestBody = $operation?->getRequestBody();
        $content = $requestBody?->getContent();
        $mediaType = $content?->offsetGet('application/json');
        $schema = $mediaType?->getSchema();

        self::assertNotNull($operation);
        self::assertSame('postTask', $operation->getOperationId());
        self::assertSame('Create task', $operation->getSummary());
        self::assertSame(['Tasks'], $operation->getTags());
        $responses = $operation->getResponses();
        self::assertIsArray($responses);
        self::assertArrayHasKey(201, $responses);
        self::assertSame('Created', $responses[201]->getDescription());
        self::assertNotNull($requestBody);
        self::assertTrue($requestBody->getRequired());
        self::assertInstanceOf(\ArrayObject::class, $content);
        self::assertTrue($content->offsetExists('application/json'));
        self::assertNotNull($mediaType);
        self::assertNotNull($schema);
        self::assertSame('#/components/schemas/TaskInput', $schema['$ref']);
    }

    public function testItReplacesAnExistingPostOperationForTheSamePath(): void
    {
        $builder = new OpenApiBuilder();
        $openApi = new OpenApi(new Info('Demo', '1.0.0'), [], new Paths());

        $openApi = $builder->addPostOperation(
            $openApi,
            '/tasks',
            'createTaskV1',
            ['Tasks'],
            ['201' => new Response('Created v1')],
            'Create task v1',
            null
        );

        $openApi = $builder->addPostOperation(
            $openApi,
            '/tasks',
            'createTaskV2',
            ['Tasks', 'Replacement'],
            ['202' => new Response('Accepted')],
            'Create task v2',
            $builder->createRequestBody('Task payload', 'TaskInput')
        );

        $operation = $openApi->getPaths()->getPath('/tasks')?->getPost();

        self::assertNotNull($operation);
        self::assertSame('createTaskV2', $operation->getOperationId());
        self::assertSame('Create task v2', $operation->getSummary());
        self::assertSame(['Tasks', 'Replacement'], $operation->getTags());
        $responses = $operation->getResponses();
        self::assertIsArray($responses);
        self::assertArrayHasKey(202, $responses);
        self::assertSame('Accepted', $responses[202]->getDescription());
        self::assertNotNull($operation->getRequestBody());
    }

    public function testItPreservesExistingMethodsWhenReplacingAPostOperationForTheSamePath(): void
    {
        $builder = new OpenApiBuilder();
        $openApi = new OpenApi(new Info('Demo', '1.0.0'), [], new Paths());

        $openApi = $builder->addGetOperation(
            $openApi,
            '/tasks',
            'getTasks',
            ['Tasks'],
            ['200' => new Response('OK')],
            'List tasks',
        );

        $openApi = $builder->addPostOperation(
            $openApi,
            '/tasks',
            'createTaskV1',
            ['Tasks'],
            ['201' => new Response('Created v1')],
            'Create task v1',
            null
        );

        $openApi = $builder->addPostOperation(
            $openApi,
            '/tasks',
            'createTaskV2',
            ['Tasks', 'Replacement'],
            ['202' => new Response('Accepted')],
            'Create task v2',
            $builder->createRequestBody('Task payload', 'TaskInput')
        );

        $path = $openApi->getPaths()->getPath('/tasks');
        $getOperation = $path?->getGet();
        $postOperation = $path?->getPost();

        self::assertNotNull($getOperation);
        self::assertSame('getTasks', $getOperation->getOperationId());
        self::assertNotNull($postOperation);
        self::assertSame('createTaskV2', $postOperation->getOperationId());
    }
}
