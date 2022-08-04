<?php

declare(strict_types=1);

namespace Linku\ApiDocumentationBundle\Sections;

final class Section
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $prefix;
    /**
     * @var string
     */
    private $title;

    public function __construct(string $name, string $prefix, string $title)
    {
        $this->name = $name;
        $this->prefix = $prefix;
        $this->title = $title;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
