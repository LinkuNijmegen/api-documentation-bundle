<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Builder;

use ApiPlatform\Core\OpenApi\Model\MediaType;
use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use ApiPlatform\Core\OpenApi\Model\RequestBody;
use ApiPlatform\Core\OpenApi\Model\Response;
use ApiPlatform\Core\OpenApi\OpenApi;

final class OpenApiBuilder
{
    public function addSchema(OpenApi $openApi, string $schemaName, array $errorSchema): OpenApi
    {
        $components = $openApi->getComponents();
        $schemas = $components->getSchemas();

        if ($schemas === null) {
            $schemas = new \ArrayObject();
        }

        $schemas[$schemaName] = new \ArrayObject($errorSchema);

        $schemas->ksort();

        return $openApi->withComponents($components->withSchemas($schemas));
    }

    public function addPostOperation(OpenApi $openApi, string $path, string $operationId, array $tags, array $responses, string $summary, ?RequestBody $requestBody): OpenApi
    {
        $operation = new Operation(
            $operationId,
            $tags,
            $responses,
            $summary,
            $summary,
            null,
            [],
            $requestBody
        );

        $paths = $openApi->getPaths();

        $paths->addPath($path, (new PathItem())->withPost($operation));

        return $openApi->withPaths($paths);
    }

    public function addGetOperation(OpenApi $openApi, string $path, string $operationId, array $tags, array $responses, string $summary): OpenApi
    {
        $operation = new Operation(
            $operationId,
            $tags,
            $responses,
            $summary,
            $summary,
            null,
            [],
            null
        );

        $paths = $openApi->getPaths();

        $paths->addPath($path, (new PathItem())->withGet($operation));

        return $openApi->withPaths($paths);
    }

    public function alterOperation(OpenApi $openApi, string $path, string $method, callable $callback): OpenApi
    {
        $paths = $openApi->getPaths();

        $pathItem = $paths->getPath($path);
        if (!$pathItem) {
            return $openApi;
        }

        $getter = 'get' . \ucfirst($method);
        $wither = 'with' . \ucfirst($method);

        if (!\method_exists($pathItem, $getter) || !\method_exists($pathItem, $wither)) {
            return $openApi;
        }

        /** @var Operation $operation */
        $operation = $pathItem->$getter();
        if (!$operation) {
            return $openApi;
        }

        $operationResponses = $operation->getResponses();

        $operation = $callback($operation);

        // Calling addPath with an existing path will override the original
        $paths->addPath($path, $pathItem->$wither($operation->withResponses($operationResponses)));

        return $openApi->withPaths($paths);
    }

    public function createResponse(string $description, ?string $reference = null): Response
    {
        $schema = null;
        if ($reference !== null) {
            $schema = new \ArrayObject([
                'application/json' => [
                    'schema' => new \ArrayObject([
                        '$ref' => '#/components/schemas/' . $reference,
                    ]),
                ],
            ]);
        }

        return new Response($description, $schema);
    }

    public function createRequestBody(string $description, string $reference): RequestBody
    {
        return new RequestBody(
            $description,
            new \ArrayObject([
                'application/json' => new MediaType(
                    new \ArrayObject([
                        '$ref' => '#/components/schemas/' . $reference,
                    ])
                ),
            ]),
            true
        );
    }
}
