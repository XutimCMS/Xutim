<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Domain\Template;

use Xutim\EditorBundle\Domain\Model\ContentBlockInterface;

final readonly class SlotDefinition
{
    /**
     * @param int $index Slot index (0, 1, 2...)
     * @param string $label Human-readable label for the slot
     * @param string|null $width Width as CSS value (e.g., "33%", "200px") or null for equal distribution
     * @param list<class-string<ContentBlockInterface>> $allowedBlockTypes Empty means all types allowed
     * @param int $minBlocks Minimum number of blocks required
     * @param int|null $maxBlocks Maximum number of blocks allowed (null = unlimited)
     */
    public function __construct(
        public int $index,
        public string $label,
        public ?string $width = null,
        public array $allowedBlockTypes = [],
        public int $minBlocks = 0,
        public ?int $maxBlocks = null,
    ) {
    }

    public function allowsBlockType(string $blockClass): bool
    {
        if ($this->allowedBlockTypes === []) {
            return true;
        }

        return in_array($blockClass, $this->allowedBlockTypes, true);
    }

    public function hasMaxLimit(): bool
    {
        return $this->maxBlocks !== null;
    }

    public function isUnlimited(): bool
    {
        return $this->maxBlocks === null;
    }
}
