<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Sections;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;

final class DocumentationFilter implements OpenApiFactoryInterface
{
    public function __construct(
        private readonly OpenApiFactoryInterface $decorated,
        private readonly ResourceMetadataCollectionFactory $resourceMetadataFactory,
        private readonly Sections $sections,
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $this->resourceMetadataFactory->enable();

        $docs = $this->decorated->__invoke($context);

        $docs = $this->addSectionTitle($docs);

        if (!$this->sections->hasMultipleSections()) {
            return $docs;
        }

        $docs = $this->appendOtherLinks($docs);

        return $docs;
    }

    private function addSectionTitle(OpenApi $docs): OpenApi
    {
        $sectionTitle = $this->sections->getCurrentSectionTitle();

        return $docs->withInfo(
            $docs->getInfo()
                ->withTitle(\str_replace('{section}', $sectionTitle, $docs->getInfo()->getTitle()))
                ->withDescription(\str_replace('{section}', $sectionTitle, $docs->getInfo()->getDescription()))
        );
    }

    private function appendOtherLinks(OpenApi $docs): OpenApi
    {
        $links = '<ul><li>'.\implode('</li><li>', $this->sections->getDocLinks()).'</li></ul>';

        return $docs->withInfo(
            $docs->getInfo()
                ->withDescription($docs->getInfo()->getDescription() . $links)
        );
    }
}
