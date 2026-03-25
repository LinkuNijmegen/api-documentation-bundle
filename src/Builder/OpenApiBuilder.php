<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Builder;

use ApiPlatform\OpenApi\Model\MediaType;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\OpenApi;

final class OpenApiBuilder
{
    /**
     * @param array<string, mixed> $errorSchema
     */
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

    /**
     * @param list<string> $tags
     * @param array<int|string, Response> $responses
     */
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
        $pathItem = $paths->getPath($path) ?? new PathItem();

        $paths->addPath($path, $pathItem->withPost($operation));

        return $openApi->withPaths($paths);
    }

    /**
     * @param list<string> $tags
     * @param array<int|string, Response> $responses
     */
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
        $pathItem = $paths->getPath($path) ?? new PathItem();

        $paths->addPath($path, $pathItem->withGet($operation));

        return $openApi->withPaths($paths);
    }

    public function alterOperation(OpenApi $openApi, string $path, string $method, callable $callback): OpenApi
    {
        $paths = $openApi->getPaths();

        $pathItem = $paths->getPath($path);
        if (!$pathItem) {
            return $openApi;
        }

        $normalizedMethod = \strtolower($method);
        $getter = 'get' . \ucfirst($normalizedMethod);
        $wither = 'with' . \ucfirst($normalizedMethod);

        if (!\method_exists($pathItem, $getter) || !\method_exists($pathItem, $wither)) {
            return $openApi;
        }

        $operation = $pathItem->$getter();
        if (!$operation instanceof Operation) {
            return $openApi;
        }

        $operation = $callback($operation);

        // Calling addPath with an existing path will override the original
        $paths->addPath($path, $pathItem->$wither($operation));

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
