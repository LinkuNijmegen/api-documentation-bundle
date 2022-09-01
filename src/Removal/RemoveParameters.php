<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Removal;

use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\OpenApi;
use Linku\ApiDocumentationBundle\Builder\OpenApiBuilder;
use Linku\ApiDocumentationBundle\Extensions\OpenApiExtension;

final class RemoveParameters implements OpenApiExtension
{
    /**
     * @var OpenApiBuilder
     */
    private $builder;
    /**
     * @var Parameter[]
     */
    private $parameters = [];

    public function __construct(OpenApiBuilder $builder, array $parameters)
    {
        $this->builder = $builder;
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
                $parameter->getPath(),
                $parameter->getMethod(),
                static function (Operation $operation) use ($parameter) {
                    $parameters = $operation->getParameters();

                    foreach ($parameters as $key => $pathParameter) {
                        if ($pathParameter->getName() === $parameter->getParameterName()) {
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
