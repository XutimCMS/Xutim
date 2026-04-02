<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dashboard;

final readonly class TranslationStat
{
    public function __construct(
        public string $label,
        public string $icon,
        public int $untranslatedCount,
        public int $outdatedCount,
        public ?string $listUrl,
        public int $unpublishedCount = 0,
    ) {
    }

    public function hasWork(): bool
    {
        return $this->untranslatedCount > 0 || $this->outdatedCount > 0 || $this->unpublishedCount > 0;
    }

    public function totalCount(): int
    {
        return $this->untranslatedCount + $this->outdatedCount + $this->unpublishedCount;
    }
}
