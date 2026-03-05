<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\BlockItemProvider;

final class BlockItemProviderRegistry
{
    /** @var array<class-string, BlockItemProviderInterface> */
    private array $providers = [];

    /**
     * @param iterable<BlockItemProviderInterface> $providers
     */
    public function __construct(iterable $providers)
    {
        foreach ($providers as $provider) {
            $this->providers[$provider->getOptionClass()] = $provider;
        }
    }

    /**
     * @param class-string $optionClass
     */
    public function get(string $optionClass): ?BlockItemProviderInterface
    {
        return $this->providers[$optionClass] ?? null;
    }

    /**
     * @param class-string $optionClass
     */
    public function has(string $optionClass): bool
    {
        return isset($this->providers[$optionClass]);
    }

    /**
     * @return array<class-string, BlockItemProviderInterface>
     */
    public function all(): array
    {
        return $this->providers;
    }
}
