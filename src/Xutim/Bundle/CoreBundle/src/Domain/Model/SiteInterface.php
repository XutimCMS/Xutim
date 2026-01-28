<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use Xutim\CoreBundle\Dto\SiteDto;

interface SiteInterface
{
    /**
     * @param array<string> $locales
     * @param array<string> $extendedContentLocales
     */
    public function change(array $locales, array $extendedContentLocales, string $theme, string $sender, string $referenceLocale): void;

    public function getReferenceLocale(): string;

    /**
     * @return array<string>
     */
    public function getLocales(): array;

    /**
     * @return array<string>
     */
    public function getContentLocales(): array;

    public function getSender(): string;

    public function toDto(): SiteDto;
}
