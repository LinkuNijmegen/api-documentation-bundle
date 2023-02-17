<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Removal;

final class RequestBody
{
    public function __construct(
        public readonly string $path,
        public readonly string $method
    ) {
    }
}
