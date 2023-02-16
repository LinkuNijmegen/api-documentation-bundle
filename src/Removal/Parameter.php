<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Removal;

final class Parameter
{
    public function __construct(
        public readonly string $path,
        public readonly string $method,
        public readonly string $parameterName
    ) {
    }
}
