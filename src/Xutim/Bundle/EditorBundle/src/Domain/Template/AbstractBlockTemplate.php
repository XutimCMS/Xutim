<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Domain\Template;

abstract class AbstractBlockTemplate implements BlockTemplateInterface
{
    /** @var list<SlotDefinition> */
    private array $slots = [];

    public function __construct()
    {
        $this->slots = $this->defineSlots();
    }

    /**
     * @return list<SlotDefinition>
     */
    abstract protected function defineSlots(): array;

    /**
     * @return list<SlotDefinition>
     */
    public function getSlots(): array
    {
        return $this->slots;
    }

    public function getSlotCount(): int
    {
        return count($this->slots);
    }

    public function getSlot(int $index): ?SlotDefinition
    {
        foreach ($this->slots as $slot) {
            if ($slot->index === $index) {
                return $slot;
            }
        }

        return null;
    }
}
