<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Removal;

final class RequestBody
{
    /**
     * @var string
     */
    private $path;
    /**
     * @var string
     */
    private $method;

    public function __construct(string $path, string $method)
    {
        $this->path = $path;
        $this->method = $method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
