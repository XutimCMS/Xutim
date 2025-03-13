<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use Doctrine\Common\Collections\Criteria;

/**
 * @template T
 */
trait TranslatableTrait
{
    /**
     * @return ?T
     */
    public function getTranslationByLocale(string $locale)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('locale', $locale))
            ->setFirstResult(0)
            ->setMaxResults(1)
        ;

        $translation = $this->translations->matching($criteria)->first();

        if ($translation === false) {
            return null;
        }

        return $translation;
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
