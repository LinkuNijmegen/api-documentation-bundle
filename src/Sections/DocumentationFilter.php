<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Sections;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;

final class DocumentationFilter implements OpenApiFactoryInterface
{
    /**
     * @var OpenApiFactoryInterface
     */
    private $decorated;

    /**
     * @var ResourceMetadataFactory
     */
    private $resourceMetadataFactory;

    /**
     * @var Sections
     */
    private $sections;

    public function __construct(OpenApiFactoryInterface $decorated, ResourceMetadataFactory $sectionResourceMetadataFactory, Sections $sections)
    {
        $this->decorated = $decorated;
        $this->resourceMetadataFactory = $sectionResourceMetadataFactory;
        $this->sections = $sections;
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
