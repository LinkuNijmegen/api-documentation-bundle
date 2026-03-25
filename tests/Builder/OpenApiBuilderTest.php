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
    public function test_it_adds_a_post_operation_and_request_body(): void
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
}
