<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Removal;

final class Response
{
    public function __construct(
        public string $path,
        public string $method,
        public int $statusCode
    ) {
    }
}
