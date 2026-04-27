<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Definition;

final class LayoutDefinitionRegistry
{
    /** @var array<string, LayoutDefinition> */
    private array $definitions = [];

    /**
     * @param iterable<LayoutDefinition> $definitions
     */
    public function __construct(iterable $definitions)
    {
        foreach ($definitions as $definition) {
            $code = $definition->getCode();
            if (isset($this->definitions[$code])) {
                throw new \LogicException(sprintf(
                    'Duplicate xutim layout definition code "%s": %s vs %s',
                    $code,
                    $this->definitions[$code]::class,
                    $definition::class
                ));
            }
            $this->definitions[$code] = $definition;
        }
    }

    public function getByCode(string $code): ?LayoutDefinition
    {
        return $this->definitions[$code] ?? null;
    }

    public function has(string $code): bool
    {
        return isset($this->definitions[$code]);
    }

    /**
     * @return array<string, LayoutDefinition>
     */
    public function all(): array
    {
        return $this->definitions;
    }
}
