<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Extensions;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\OpenApi;
use Linku\ApiDocumentationBundle\Sections\Sections;

final class OpenApiExtender implements OpenApiFactoryInterface
{
    /**
     * @var OpenApiFactoryInterface
     */
    private $decorated;

    /**
     * @var Sections
     */
    private $sections;

    /**
     * @var OpenApiExtension[]
     */
    private $extensions;

    public function __construct(OpenApiFactoryInterface $decorated, Sections $sections, iterable $extensions = [])
    {
        $this->decorated = $decorated;
        $this->sections = $sections;
        $this->extensions = $extensions;
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
                && !\in_array($this->sections->getCurrentSection()->getName(), $extension->getSupportedSections())) {
                continue;
            }

            $docs = $extension($docs);
        }

        return $docs;
    }
}
