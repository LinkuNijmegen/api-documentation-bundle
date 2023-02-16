<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Extensions;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
use Linku\ApiDocumentationBundle\Sections\Sections;

final class OpenApiExtender implements OpenApiFactoryInterface
{
    public function __construct(
        private readonly OpenApiFactoryInterface $decorated,
        private readonly Sections $sections,
        /** @var OpenApiExtension[] */
        private readonly iterable $extensions = []
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $docs = $this->decorated->__invoke($context);

        foreach ($this->extensions as $extension) {
            if (!$extension instanceof OpenApiExtension) {
                continue;
            }

            if ($extension instanceof SectionedOpenApiExtension
                && $this->sections->hasMultipleSections()
                && !\in_array($this->sections->getCurrentSection()->name, $extension->getSupportedSections())) {
                continue;
            }

            $docs = $extension($docs);
        }

        return $docs;
    }
}
