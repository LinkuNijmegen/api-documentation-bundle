<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Removal;

final class Parameter
{
    /**
     * @var string
     */
    private $path;
    /**
     * @var string
     */
    private $method;
    /**
     * @var string
     */
    private $parameterName;

    public function __construct(string $path, string $method, string $parameterName)
    {
        $this->path = $path;
        $this->method = $method;
        $this->parameterName = $parameterName;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getParameterName(): string
    {
        return $this->parameterName;
    }
}
