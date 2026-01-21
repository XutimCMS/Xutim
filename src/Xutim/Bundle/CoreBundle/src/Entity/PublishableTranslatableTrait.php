<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;

/**
 * @template TKey
 * @template T of ContentTranslationInterface
 */
trait PublishableTranslatableTrait
{
    /**
     * @return T
     */
    public function getTranslationByLocaleOrDefault(string $locale)
    {
        $trans = $this->translations
            ->filter(fn ($trans) => $trans->getLocale() === $locale);

        if ($trans->first() === false) {
            return $this->defaultTranslation;
        }

        return $trans->first();
    }

    /**
     * @return T
     */
    public function getPublishedTranslationByLocaleOrFallback(string $locale, string $altLocale)
    {
        $trans = $this->translations
            ->filter(fn ($trans) => $trans->getLocale() === $locale);

        if ($trans->first() === false) {
            $fallbackTrans = $this->getTranslationByLocale($altLocale);
            if ($fallbackTrans !== null) {
                return $fallbackTrans;
            }
            return $this->defaultTranslation;
        }

        return $trans->first();
    }

    /**
     * @return ?T
     */
    public function getPublishedTranslationByLocale(string $locale)
    {
        $trans = $this->getPublishedTranslations()
            ->filter(fn ($trans) => $trans->getLocale() === $locale);
        if ($trans->isEmpty()) {
            return null;
        }

        return $trans->first();
    }

    /**
     * @return ?T
     */
    public function getPublishedTranslationByLocaleOrAny(string $locale)
    {
        $translations = $this->getPublishedTranslations();
        if ($translations->isEmpty()) {
            return null;
        }
        $localeTrans = $translations->filter(fn ($trans) => $trans->getLocale() === $locale);
        if ($localeTrans->isEmpty()) {
            return $translations->first();
        }

        return $localeTrans->first();
    }

    /**
     * @return Collection<TKey, T>
     */
    public function getPublishedTranslations(): Collection
    {
        return $this->translations
            ->filter(fn ($trans) => $trans->isPublished() === true);
    }
}
