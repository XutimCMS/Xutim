<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Domain\Template;

interface BlockTemplateInterface
{
    public function getName(): string;

    public function getLabel(): string;

    public function getDescription(): string;

    /**
     * @return list<SlotDefinition>
     */
    public function getSlots(): array;

    public function getSlotCount(): int;

    public function getSlot(int $index): ?SlotDefinition;
}
