<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Extensions;

use ApiPlatform\Core\OpenApi\OpenApi;

interface OpenApiExtension
{
    public function __invoke(OpenApi $docs): OpenApi;
}
