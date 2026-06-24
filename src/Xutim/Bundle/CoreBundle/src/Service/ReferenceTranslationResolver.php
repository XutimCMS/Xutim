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
     * @template T of LocaleAwareInterface
     * @param TranslatableInterface<T> $entity
     * @return T|null
     */
    public function resolve(TranslatableInterface $entity): ?LocaleAwareInterface
    {
        $refLocale = $this->siteContext->getReferenceLocale();
        foreach ($entity->getTranslations() as $trans) {
            if ($trans->getLocale() === $refLocale) {
                return $trans;
            }
        }

        return null;
    }

    /**
     * @template T of LocaleAwareInterface
     * @param TranslatableInterface<T> $entity
     * @return T|null
     */
    public function resolveOrAny(TranslatableInterface $entity): ?LocaleAwareInterface
    {
        $ref = $this->resolve($entity);
        if ($ref !== null) {
            return $ref;
        }
        foreach ($entity->getTranslations() as $trans) {
            return $trans;
        }

        return null;
    }
}
