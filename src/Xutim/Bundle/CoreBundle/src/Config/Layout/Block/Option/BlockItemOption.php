<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Config\Layout\LayoutConfigItem;
use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

interface BlockItemOption extends LayoutConfigItem
{
    /**
     * Decides if the given item meets all requirements to pass the
     * option check.
     */
    public function canFullFill(BlockItemInterface $item): bool;

    /**
     * Returns a name that is used in an admin ui to see block
     * requirements.
     */
    public function getName(): string;

    /**
     * Whether the value held by this option type should be translated
     * when content is auto-translated across locales. Text-like fields
     * return true; references (image, page, link, …) return false.
     */
    public function isTranslatable(): bool;

    /**
     * Optional short description shown alongside `getName()` in admin
     * pickers. Null means no description.
     */
    public function getDescription(): ?string;
}
