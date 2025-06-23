<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

readonly class BlockItemOptionComposed implements BlockItemOption
{
    /** @var array<BlockItemOption> */
    private array $options;

    public function __construct(
        BlockItemOption ...$options
    ) {
        $this->options = $options;
    }

    public function canFullFill(BlockItemInterface $item): bool
    {
        foreach ($this->options as $option) {
            if ($option->canFullFill($item) === false) {
                return false;
            }
        }

        return true;
    }

    public function getName(): string
    {
        $names = array_map(fn (BlockItemOption $opt) => $opt->getName(), $this->options);

        return sprintf(
            'Item that is a combination of %d items: %s',
            count($this->options),
            implode(', ', $names)
        );
    }

    /**
     * @return array<BlockItemOption>
     */
    public function getDecomposedOptions(): array
    {
        return $this->options;
    }
}
