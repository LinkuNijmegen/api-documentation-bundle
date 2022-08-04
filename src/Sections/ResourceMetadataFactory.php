<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Sections;

use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;

final class ResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
    /**
     * @var ResourceMetadataFactoryInterface
     */
    private $decorated;

    /**
     * @var Sections
     */
    private $sections;

    /**
     * Prevent filtering while building cache
     *
     * @var bool
     */
    private $enabled = false;

    public function __construct(ResourceMetadataFactoryInterface $decorated, Sections $sections)
    {
        $this->decorated = $decorated;
        $this->sections = $sections;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function create(string $resourceClass): ResourceMetadata
    {
        $metadata = $this->decorated->create($resourceClass);

        // Prevent filtering while building cache
        if (!$this->enabled) {
            return $metadata;
        }

        // If entire resource has a 'sections' attribute and is not in current section, remove all paths
        $metadataSections = $metadata->getAttribute('sections');
        if ($metadataSections !== null) {
            if ($this->sections->isCurrentSectionAllowed($metadataSections)) {
                return $metadata; // Don't filter subitems when the entire resource is allowed
            }

            return $metadata
                ->withItemOperations([])
                ->withCollectionOperations([])
                ->withSubresourceOperations([]);
        }

        // Else, check each operation individually

        if ($metadata->getItemOperations() !== null) {
            $metadata = $metadata->withItemOperations($this->filterOperations($metadata->getItemOperations()));
        }

        if ($metadata->getCollectionOperations() !== null) {
            $metadata = $metadata->withCollectionOperations($this->filterOperations($metadata->getCollectionOperations()));
        }

        if ($metadata->getSubresourceOperations() !== null) {
            $metadata = $metadata->withSubresourceOperations($this->filterOperations($metadata->getSubresourceOperations()));
        }

        return $metadata;
    }

    private function filterOperations(array $operations): array
    {
        foreach ($operations as $name => $operation) {
            $sections = $operation['sections'] ?? null;
            $path = $operation['path'] ?? null;

            // If a set of sections is defined for this operation, unset it if none of these sections is the current one
            if ($sections !== null) {
                if (!$this->sections->isCurrentSectionAllowed($sections)) {
                    unset($operations[$name]);
                }

                continue;
            }

            // If a path is defined, check it against the current section
            if ($path !== null) {
                if (!$this->sections->isPathInCurrentSection($path)) {
                    unset($operations[$name]);
                }

                continue;
            }

            // Only allow default paths with the default section
            if (!$this->sections->isCurrentSectionDefault()) {
                unset($operations[$name]);
            }
        }

        return $operations;
    }
}
