<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

interface TranslationLocaleAwareInterface
{
    /** @return list<string> */
    public function getTranslationLocales(): array;

    public function hasAllTranslationLocales(): bool;

    public function changeAllTranslationLocales(bool $allTranslationLocales): void;

    public function isLocaleAllowed(string $locale): bool;

    /** @param list<string> $locales */
    public function changeTranslationLocales(array $locales): void;
}
