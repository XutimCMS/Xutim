<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\LocaleAwareInterface;
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
     * @param TranslatableInterface<T> $entity
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
     * @param TranslatableInterface<T> $entity
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
}
