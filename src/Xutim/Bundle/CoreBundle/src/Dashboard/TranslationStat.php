<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dashboard;

final readonly class TranslationStat
{
    /**
     * @param array<string, LocaleStat> $localeBreakdown per-locale counts and links (optional)
     */
    public function __construct(
        public string $label,
        public string $icon,
        public int $untranslatedCount,
        public int $outdatedCount,
        public ?string $listUrl,
        public int $unpublishedCount = 0,
        public array $localeBreakdown = [],
        public ?string $untranslatedUrl = null,
        public ?string $outdatedUrl = null,
        public ?string $unpublishedUrl = null,
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
