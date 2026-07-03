<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\LocaleAwareInterface;
use Xutim\CoreBundle\Domain\Model\PublishableTranslationInterface;
use Xutim\CoreBundle\Domain\Model\TranslatableInterface;

final readonly class ReferenceTranslationResolver
{
    public function __construct(private SiteContext $siteContext)
    {
    }

    /**
     * Site reference-locale translation, else first available.
     * Caller must guarantee the entity has at least one translation.
     *
     * @template T of LocaleAwareInterface
     * @param  TranslatableInterface<T> $entity
     * @return T
     */
    public function resolve(TranslatableInterface $entity): LocaleAwareInterface
    {
        $refLocale = $this->siteContext->getReferenceLocale();
        $first = null;
        foreach ($entity->getTranslations() as $trans) {
            if ($trans->getLocale() === $refLocale) {
                return $trans;
            }
            $first ??= $trans;
        }
        assert($first !== null, 'Translatable entity must have at least one translation');

        return $first;
    }

    /**
     * Translation in the given locale, else site reference locale, else first available.
     * Caller must guarantee the entity has at least one translation.
     *
     * @template T of LocaleAwareInterface
     * @param  TranslatableInterface<T> $entity
     * @return T
     */
    public function resolveByLocale(TranslatableInterface $entity, string $locale): LocaleAwareInterface
    {
        $refLocale = $this->siteContext->getReferenceLocale();
        $ref = null;
        $first = null;
        foreach ($entity->getTranslations() as $trans) {
            if ($trans->getLocale() === $locale) {
                return $trans;
            }
            if ($trans->getLocale() === $refLocale) {
                $ref = $trans;
            }
            $first ??= $trans;
        }
        if ($ref !== null) {
            return $ref;
        }
        assert($first !== null, 'Translatable entity must have at least one translation');

        return $first;
    }

    /**
     * Published reference-locale translation, else first published. Null when nothing is published.
     *
     * @template T of LocaleAwareInterface&PublishableTranslationInterface
     * @param  TranslatableInterface<T> $entity
     * @return T|null
     */
    public function resolvePublished(TranslatableInterface $entity): ?LocaleAwareInterface
    {
        $refLocale = $this->siteContext->getReferenceLocale();
        $first = null;
        foreach ($entity->getTranslations() as $trans) {
            if (!$trans->isPublished()) {
                continue;
            }
            if ($trans->getLocale() === $refLocale) {
                return $trans;
            }
            $first ??= $trans;
        }

        return $first;
    }

    /**
     * Published translation in exactly the given locale, or null. No fallback.
     *
     * @template T of LocaleAwareInterface&PublishableTranslationInterface
     * @param  TranslatableInterface<T> $entity
     * @return T|null
     */
    public function resolvePublishedInLocale(TranslatableInterface $entity, string $locale): ?LocaleAwareInterface
    {
        foreach ($entity->getTranslations() as $trans) {
            if ($trans->getLocale() === $locale && $trans->isPublished()) {
                return $trans;
            }
        }

        return null;
    }

    /**
     * Published translation in the given locale, else published reference locale, else first published.
     * Null when nothing is published.
     *
     * @template T of LocaleAwareInterface&PublishableTranslationInterface
     * @param  TranslatableInterface<T> $entity
     * @return T|null
     */
    public function resolvePublishedByLocale(TranslatableInterface $entity, string $locale): ?LocaleAwareInterface
    {
        $refLocale = $this->siteContext->getReferenceLocale();
        $ref = null;
        $first = null;
        foreach ($entity->getTranslations() as $trans) {
            if (!$trans->isPublished()) {
                continue;
            }
            if ($trans->getLocale() === $locale) {
                return $trans;
            }
            if ($trans->getLocale() === $refLocale) {
                $ref = $trans;
            }
            $first ??= $trans;
        }

        return $ref ?? $first;
    }
}
