<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Removal;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\OpenApi;
use Linku\ApiDocumentationBundle\Builder\OpenApiBuilder;
use Linku\ApiDocumentationBundle\Extensions\OpenApiExtension;

final class RemoveParameters implements OpenApiExtension
{
    /**
     * @var Parameter[]
     */
    private array $parameters = [];

    public function __construct(
        private readonly OpenApiBuilder $builder,
        array $parameters,
    ) {
        foreach ($parameters as $parameterData) {
            $this->parameters[] = new Parameter(
                $parameterData['path'],
                $parameterData['method'],
                $parameterData['name']
            );
        }
    }

    public function __invoke(OpenApi $docs): OpenApi
    {
        foreach ($this->parameters as $parameter) {
            $docs = $this->builder->alterOperation(
                $docs,
                $parameter->path,
                $parameter->method,
                static function (Operation $operation) use ($parameter) {
                    $parameters = $operation->getParameters();

                    foreach ($parameters as $key => $pathParameter) {
                        if ($pathParameter->getName() === $parameter->parameterName) {
                            unset($parameters[$key]);
                        }
                    }

                    return $operation->withParameters($parameters);
                }
            );
        }

        return $docs;
    }
}
