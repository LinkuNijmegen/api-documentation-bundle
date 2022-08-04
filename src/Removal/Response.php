<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Removal;

final class Response
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
     * @var int
     */
    private $statusCode;

    public function __construct(string $path, string $method, int $statusCode)
    {
        $this->path = $path;
        $this->method = $method;
        $this->statusCode = $statusCode;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
