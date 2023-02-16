<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Sections;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class Sections
{
    /**
     * @var Section[]
     */
    private array $sections = [];
    private Section $defaultSection;
    private string $sectionPathRegex;

    /**
     * @throws \RuntimeException
     */
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $urlGenerator,
        array $sections,
        ?string $defaultSectionName = null
    ) {
        if (!\count($sections)) {
            throw new \RuntimeException('Needs at least 1 section'); // @TODO: Create own exception
        }

        foreach ($sections as $key => $sectionData) {
            $this->sections[$key] = new Section((string) $key, $sectionData['prefix'], $sectionData['title']);
        }

        if ($defaultSectionName === null) {
            $defaultSectionName = (string) \array_key_first($this->sections);
        }

        if (!isset($this->sections[$defaultSectionName])) {
            throw new \RuntimeException(\sprintf('Given default section "%s" does not exist', $defaultSectionName)); // @TODO: Create own exception
        }

        $this->defaultSection = $this->sections[$defaultSectionName];

        $this->sectionPathRegex = \sprintf(
            '/^\/(%s)\//',
            \implode(
                '|',
                \array_filter(
                    \array_map(
                        static function (Section $section) {
                            return $section->prefix;
                        },
                        $this->sections
                    )
                )
            )
        );
    }

    public function getCurrentSection(): Section
    {
        if ($this->requestStack->getMainRequest() === null) {
            return $this->defaultSection;
        }

        $prefix = $this->requestStack->getMainRequest()->attributes->get('section', '');

        return $this->getSectionFromPrefix($prefix) ?? $this->defaultSection;
    }

    public function getSectionFromPrefix($prefix): ?Section
    {
        foreach ($this->sections as $section) {
            if ($section->prefix === $prefix) {
                return $section;
            }
        }

        return null;
    }

    public function hasMultipleSections(): bool
    {
        return \count($this->sections) > 1;
    }

    public function isPathInCurrentSection(string $path): bool
    {
        $currentSection = $this->getCurrentSection();

        // If a matching section is found, check it against the given section
        if (\preg_match($this->sectionPathRegex, $path, $matches)) {
            return ($matches[1] === $currentSection->prefix);
        }

        // If no matching section is found in the path, return true when current section is default
        return $this->isCurrentSectionDefault();
    }

    public function getCurrentSectionTitle(): string
    {
        return $this->getCurrentSection()->title;
    }

    public function isSectionCurrent(string $sectionName): bool
    {
        return $this->getCurrentSection()->name === $sectionName;
    }

    public function isCurrentSectionDefault(): bool
    {
        return $this->getCurrentSection() === $this->defaultSection;
    }

    public function isCurrentSectionAllowed(array $allowedSections): bool
    {
        // No selection means 'allow all'
        if ($allowedSections === []) {
            return true;
        }

        return \in_array($this->getCurrentSection()->name, $allowedSections, true);
    }

    public function getDocLinks(): array
    {
        $links = [];
        foreach ($this->sections as $section) {
            $isDefault = $section === $this->defaultSection;

            $links[$section->name] = \sprintf(
                '<a href="%s">%s</a>',
                $this->urlGenerator->generate(
                    $isDefault ? 'api_doc' : 'linku_api_documentation_section_docs',
                    $isDefault ? [] : ['section' => $section->prefix]
                ),
                $section->title
            );
        }
        return $links;
    }
}
