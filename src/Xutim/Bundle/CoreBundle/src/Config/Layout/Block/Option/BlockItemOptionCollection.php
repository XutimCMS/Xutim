<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

readonly class BlockItemOptionCollection implements BlockItemOption
{
    public function __construct(private BlockItemOption $option)
    {
    }

    public function canFullFill(BlockItemInterface $item): bool
    {
        return $this->option->canFullFill($item);
    }

    public function getName(): string
    {
        return sprintf('collection (0 or more items) of %ss', $this->option->getName());
    }

    public function isTranslatable(): bool
    {
        return $this->option->isTranslatable();
    }

    public function getDescription(): ?string
    {
        return $this->option->getDescription();
    }

    public function getOption(): BlockItemOption
    {
        return $this->option;
    }

    /**
     * @return array<BlockItemOption>
    */
    public function getDecomposedOptions(): array
    {
        if ($this->option instanceof BlockItemOptionUnion ||
            $this->option instanceof BlockItemOptionComposed
        ) {
            return $this->option->getDecomposedOptions();
        }

        return [$this->option];
    }
}
