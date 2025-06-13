<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

/**
 * @template T
 */
trait BasicTranslatableTrait
{
    /**
     * @return ?T
     */
    public function getTranslationByLocale(string $locale)
    {
        $translations = $this->translations
            ->filter(fn ($trans) => $trans->getLocale() === $locale);
        
        if ($translations->first() === false) {
            return null;
        }

        return $translations->first();
    }

    /**
     * @return T
     */
    public function getTranslationByLocaleOrAny(string $locale)
    {
        $translation = $this->getTranslationByLocale($locale);

        if ($translation === null) {
            /** @var T */
            return $this->translations->first();
        }

        return $translation;
    }
}
