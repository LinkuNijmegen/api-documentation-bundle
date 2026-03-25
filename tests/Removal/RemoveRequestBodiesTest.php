<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Tests\Removal;

use ApiPlatform\OpenApi\Model\ExternalDocumentation;
use ApiPlatform\OpenApi\Model\Info;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\OpenApi;
use Linku\ApiDocumentationBundle\Builder\OpenApiBuilder;
use Linku\ApiDocumentationBundle\Removal\RemoveRequestBodies;
use PHPUnit\Framework\TestCase;

final class RemoveRequestBodiesTest extends TestCase
{
    public function test_it_removes_the_request_body_without_losing_operation_metadata(): void
    {
        $externalDocs = new ExternalDocumentation('Task docs', 'https://example.com/tasks');
        $callbacks = new \ArrayObject(['taskCallback' => new \ArrayObject(['status' => 'done'])]);

        $operation = (new Operation(
            operationId: 'completeTask',
            tags: ['Tasks'],
            responses: ['200' => new Response('Done')],
            summary: 'Complete task',
            description: 'Complete task',
            externalDocs: $externalDocs,
            parameters: [new Parameter('id', 'path', 'Task id', true)],
            requestBody: new RequestBody('Body', new \ArrayObject(), true),
            callbacks: $callbacks,
            deprecated: true,
            security: [['bearerAuth' => []]],
            servers: [['url' => 'https://api.example.com']],
        ))->withExtensionProperty('section', 'tasks');

        $openApi = new OpenApi(new Info('Demo', '1.0.0'), [], new Paths());
        $openApi->getPaths()->addPath('/tasks/{id}/complete', (new PathItem())->withPost($operation));

        $extension = new RemoveRequestBodies(
            new OpenApiBuilder(),
            [[
                'path' => '/tasks/{id}/complete',
                'method' => 'POST',
            ]]
        );

        $updatedOpenApi = $extension($openApi);
        $updatedOperation = $updatedOpenApi->getPaths()->getPath('/tasks/{id}/complete')?->getPost();

        self::assertNotNull($updatedOperation);
        self::assertNull($updatedOperation->getRequestBody());
        self::assertSame($operation->getOperationId(), $updatedOperation->getOperationId());
        self::assertSame($operation->getSummary(), $updatedOperation->getSummary());
        self::assertSame($operation->getDescription(), $updatedOperation->getDescription());
        self::assertSame($operation->getTags(), $updatedOperation->getTags());
        self::assertSame($operation->getResponses(), $updatedOperation->getResponses());
        self::assertSame($operation->getParameters(), $updatedOperation->getParameters());
        self::assertSame($operation->getExternalDocs(), $updatedOperation->getExternalDocs());
        self::assertSame($operation->getCallbacks(), $updatedOperation->getCallbacks());
        self::assertSame($operation->getDeprecated(), $updatedOperation->getDeprecated());
        self::assertSame($operation->getSecurity(), $updatedOperation->getSecurity());
        self::assertSame($operation->getServers(), $updatedOperation->getServers());
        self::assertSame($operation->getExtensionProperties(), $updatedOperation->getExtensionProperties());
    }
}
