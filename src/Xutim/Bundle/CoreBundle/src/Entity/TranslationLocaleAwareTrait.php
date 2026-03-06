<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

trait TranslationLocaleAwareTrait
{
    /** @return list<string> */
    public function getTranslationLocales(): array
    {
        return $this->translationLocales;
    }

    public function hasAllTranslationLocales(): bool
    {
        return $this->allTranslationLocales;
    }

    public function changeAllTranslationLocales(bool $allTranslationLocales): void
    {
        $this->allTranslationLocales = $allTranslationLocales;
    }

    public function isLocaleAllowed(string $locale): bool
    {
        if ($this->allTranslationLocales) {
            return true;
        }

        return in_array($locale, $this->translationLocales, true);
    }

    /** @param list<string> $locales */
    public function changeTranslationLocales(array $locales): void
    {
        $this->translationLocales = $locales;
    }
}
