<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Sections;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class Sections
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var Section[]
     */
    private $sections = [];

    /**
     * @var Section
     */
    private $defaultSection;

    /**
     * @var string
     */
    private $sectionPathRegex;

    public function __construct(RequestStack $requestStack, UrlGeneratorInterface $urlGenerator, array $sections, ?string $defaultSection = null)
    {
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;

        if (!\count($sections)) {
            throw new \Exception('Needs at least 1 section'); // @TODO: Create own exception
        }

        foreach ($sections as $key => $sectionData) {
            $this->sections[$key] = new Section((string)$key, $sectionData['prefix'], $sectionData['title']);
        }

        if ($defaultSection === null) {
            $defaultSection = \array_key_first($this->sections);
        }

        if (!isset($this->sections[$defaultSection])) {
            throw new \Exception(\sprintf('Given default section "%s" does not exist', $defaultSection)); // @TODO: Create own exception
        }

        $this->defaultSection = $this->sections[$defaultSection];

        $this->sectionPathRegex = \sprintf(
            '/^\/(%s)\//',
            \implode(
                '|',
                \array_filter(
                    \array_map(
                        static function (Section $section) {
                            return $section->getPrefix();
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

        foreach ($this->sections as $section) {
            if ($section->getPrefix() === $prefix) {
                return $section;
            }
        }

        return $section;
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
            return ($matches[1] === $currentSection->getPrefix());
        }

        // If no matching section is found in the path, return true when current section is default
        return $this->isCurrentSectionDefault();
    }

    public function getCurrentSectionTitle(): string
    {
        return $this->getCurrentSection()->getTitle();
    }

    public function isSectionCurrent(string $sectionName): bool
    {
        return $this->getCurrentSection()->getName() === $sectionName;
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

        return \in_array($this->getCurrentSection()->getName(), $allowedSections, true);
    }

    public function getDocLinks(): array
    {
        $links = [];
        foreach ($this->sections as $section) {
            $isDefault = $section === $this->defaultSection;

            $links[$section->getName()] = \sprintf(
                '<a href="%s">%s</a>',
                $this->urlGenerator->generate(
                    $isDefault?'api_doc':'linku_api_documentation_section_docs',
                    $isDefault?[]:['section' => $section->getPrefix()]
                ),
                $section->getTitle()
            );
        }
        return $links;
    }
}
