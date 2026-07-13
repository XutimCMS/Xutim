<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\SectionFieldProvider;

use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;

final class SectionFieldProviderRegistry
{
    /** @var array<class-string, SectionFieldProviderInterface> */
    private array $providers = [];

    /**
     * @param iterable<SectionFieldProviderInterface> $providers
     */
    public function __construct(iterable $providers)
    {
        foreach ($providers as $provider) {
            $this->providers[$provider->getOptionClass()] = $provider;
        }
    }

    public function getForOption(BlockItemOption $option): SectionFieldProviderInterface
    {
        $class = $option::class;
        if (!isset($this->providers[$class])) {
            throw new \LogicException(sprintf(
                'No SectionFieldProvider registered for option "%s"',
                $class
            ));
        }

        return $this->providers[$class];
    }

    /**
     * @param class-string<BlockItemOption> $optionClass
     */
    public function has(string $optionClass): bool
    {
        return isset($this->providers[$optionClass]);
    }
}
