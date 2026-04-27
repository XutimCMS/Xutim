<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\LayoutFieldProvider;

use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;

final class LayoutFieldProviderRegistry
{
    /** @var array<class-string, LayoutFieldProviderInterface> */
    private array $providers = [];

    /**
     * @param iterable<LayoutFieldProviderInterface> $providers
     */
    public function __construct(iterable $providers)
    {
        foreach ($providers as $provider) {
            $this->providers[$provider->getOptionClass()] = $provider;
        }
    }

    public function getForOption(BlockItemOption $option): LayoutFieldProviderInterface
    {
        $class = $option::class;
        if (!isset($this->providers[$class])) {
            throw new \LogicException(sprintf(
                'No LayoutFieldProvider registered for option "%s"',
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
