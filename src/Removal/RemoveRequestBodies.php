<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Removal;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\OpenApi;
use Linku\ApiDocumentationBundle\Builder\OpenApiBuilder;
use Linku\ApiDocumentationBundle\Extensions\OpenApiExtension;

final class RemoveRequestBodies implements OpenApiExtension
{
    /**
     * @var OpenApiBuilder
     */
    private $builder;
    /**
     * @var Parameter[]
     */
    private $requestBodies = [];

    public function __construct(OpenApiBuilder $builder, array $requestBodies)
    {
        $this->builder = $builder;
        foreach ($requestBodies as $requestBodyData) {
            $this->requestBodies[] = new RequestBody(
                $requestBodyData['path'],
                $requestBodyData['method']
            );
        }
    }

    public function __invoke(OpenApi $docs): OpenApi
    {
        foreach ($this->requestBodies as $requestBody) {
            $docs = $this->builder->alterOperation(
                $docs,
                $requestBody->getPath(),
                $requestBody->getMethod(),
                static function (Operation $operation) {
                    // Just because we can't pass `null` through `withRequestBody`
                    return new Operation(
                        $operation->getOperationId(),
                        $operation->getTags(),
                        $operation->getResponses(),
                        $operation->getSummary(),
                        $operation->getDescription(),
                        $operation->getExternalDocs(),
                        $operation->getParameters(),
                        null,
                        $operation->getCallbacks(),
                        $operation->getDeprecated(),
                        $operation->getSecurity(),
                        $operation->getServers(),
                        $operation->getExtensionProperties()
                    );
                }
            );
        }

        return $docs;
    }
}
