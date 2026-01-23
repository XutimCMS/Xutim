<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Domain\Template;

class BlockTemplateRegistry
{
    /** @var array<string, BlockTemplateInterface> */
    private array $templates = [];

    /**
     * @param iterable<BlockTemplateInterface> $templates
     */
    public function __construct(iterable $templates)
    {
        foreach ($templates as $template) {
            $this->templates[$template->getName()] = $template;
        }
    }

    public function get(string $name): ?BlockTemplateInterface
    {
        return $this->templates[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->templates[$name]);
    }

    /**
     * @return array<string, BlockTemplateInterface>
     */
    public function all(): array
    {
        return $this->templates;
    }

    /**
     * @return list<string>
     */
    public function getNames(): array
    {
        return array_keys($this->templates);
    }
}
