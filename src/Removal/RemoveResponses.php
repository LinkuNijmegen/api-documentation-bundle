<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Removal;

use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\OpenApi;
use Linku\ApiDocumentationBundle\Builder\OpenApiBuilder;
use Linku\ApiDocumentationBundle\Extensions\OpenApiExtension;

final class RemoveResponses implements OpenApiExtension
{
    /**
     * @var OpenApiBuilder
     */
    private $builder;
    /**
     * @var Response[]
     */
    private $responses = [];

    public function __construct(OpenApiBuilder $builder, array $responses)
    {
        $this->builder = $builder;
        foreach ($responses as $responseData) {
            $this->responses[] = new Response($responseData['path'], $responseData['method'], $responseData['statusCode']);
        }
    }

    public function __invoke(OpenApi $docs): OpenApi
    {
        foreach ($this->responses as $response) {
            $docs = $this->builder->alterOperation(
                $docs,
                $response->getPath(),
                $response->getMethod(),
                static function (Operation $operation) use ($response) {
                    $responses = $operation->getResponses();

                    unset($responses[$response->getStatusCode()]);

                    return $operation->withResponses($responses);
                }
            );
        }

        return $docs;
    }
}
