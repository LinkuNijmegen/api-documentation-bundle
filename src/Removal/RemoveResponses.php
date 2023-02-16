<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Removal;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\OpenApi;
use Linku\ApiDocumentationBundle\Builder\OpenApiBuilder;
use Linku\ApiDocumentationBundle\Extensions\OpenApiExtension;

final class RemoveResponses implements OpenApiExtension
{
    /**
     * @var Response[]
     */
    private array $responses = [];

    public function __construct(
        private readonly OpenApiBuilder $builder,
        array $responses
    ) {
        foreach ($responses as $responseData) {
            $this->responses[] = new Response($responseData['path'], $responseData['method'], $responseData['statusCode']);
        }
    }

    public function __invoke(OpenApi $docs): OpenApi
    {
        foreach ($this->responses as $response) {
            $docs = $this->builder->alterOperation(
                $docs,
                $response->path,
                $response->method,
                static function (Operation $operation) use ($response) {
                    $responses = $operation->getResponses();

                    unset($responses[$response->statusCode]);

                    return $operation->withResponses($responses);
                }
            );
        }

        return $docs;
    }
}
