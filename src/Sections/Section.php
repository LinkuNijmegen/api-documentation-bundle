<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Sections;

final class Section
{
    public function __construct(
        public readonly string $name,
        public readonly string $prefix,
        public readonly string $title
    ) {
    }
}
