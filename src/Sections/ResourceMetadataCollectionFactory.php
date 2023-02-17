<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Sections;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Operations;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;

final class ResourceMetadataCollectionFactory implements ResourceMetadataCollectionFactoryInterface
{
    public function __construct(
        private readonly ResourceMetadataCollectionFactoryInterface $decorated,
        private readonly Sections $sections,
        /** Prevent filtering while building cache */
        private bool $enabled = false,
    ) {
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function create(string $resourceClass): ResourceMetadataCollection
    {
        $metadataCollection = $this->decorated->create($resourceClass);

        // Prevent filtering while building cache
        if (!$this->enabled) {
            return $metadataCollection;
        }

        /** @var ApiResource $metadata */
        foreach ($metadataCollection as $i => $metadata) {
            $metadataCollection->offsetSet($i, $this->filterResource($metadata));
        }

        return $metadataCollection;
    }

    private function filterResource(ApiResource $resource): ApiResource
    {
        // If entire resource has a 'sections' or 'route_prefix' attribute and is not in current section, remove all paths
        $metadataSections = $this->getSectionsFromResource($resource);
        if ($metadataSections !== null) {
            if ($this->sections->isCurrentSectionAllowed($metadataSections)) {
                return $resource; // Don't filter subitems when the entire resource is allowed
            }

            return $resource->withOperations(new Operations([]));
        }

        return $resource->withOperations($this->filterOperations($operations));
    }

    private function getSectionsFromResource(ApiResource $resource): ?array
    {
        // Check sections attribute first
        $sections = $resource->getExtraProperties()['sections'] ?? null;
        if ($sections !== null) {
            return $sections;
        }

        // If that isn't used, check the route_prefix attribute
        $prefix = $resource->getRoutePrefix();
        if ($prefix !== null) {
            $section = $this->sections->getSectionFromPrefix(\ltrim($prefix, '/'));
            if ($section !== null) {
                return [$section->name];
            }
        }

        return null;
    }

    private function filterOperations(?Operations $operations): Operations
    {
        if ($operations === null) {
            return new Operations([]);
        }

        /** @var Operation $operation */
        foreach ($operations as $name => $operation) {
            $sections = $operation->getExtraProperties()['sections'] ?? null;
            $path = $operation->getExtraProperties()['uriTemplate'] ?? null;

            // If a set of sections is defined for this operation, unset it if none of these sections is the current one
            if ($sections !== null) {
                if (!$this->sections->isCurrentSectionAllowed($sections)) {
                    $operations->remove($name);
                }

                continue;
            }

            // If a path is defined, check it against the current section
            if ($path !== null) {
                if (!$this->sections->isPathInCurrentSection($path)) {
                    $operations->remove($name);
                }

                continue;
            }

            // Only allow default paths with the default section
            if (!$this->sections->isCurrentSectionDefault()) {
                $operations->remove($name);
            }
        }

        return $operations;
    }
}
