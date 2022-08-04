<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Extensions;

interface SectionedOpenApiExtension extends OpenApiExtension
{
    /**
     * Return an array of supported section names
     *
     * @return string[]
     */
    public function getSupportedSections(): array;
}
