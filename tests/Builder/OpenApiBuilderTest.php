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

        self::assertNotNull($operation);
        self::assertSame('postTask', $operation->getOperationId());
        self::assertSame('Create task', $operation->getSummary());
        self::assertNotNull($operation->getRequestBody());
    }
}
