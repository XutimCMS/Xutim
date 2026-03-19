<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;

final readonly class ReferenceTranslationLocaleResolver
{
    public function __construct(private SiteContext $siteContext)
    {
    }

    /**
     * @return list<string>
     */
    public function resolveStaleLocales(ContentTranslationInterface $referenceTranslation): array
    {
        $content = $referenceTranslation->getObject();
        $referenceLocale = $referenceTranslation->getLocale();
        $candidateLocales = $content->hasAllTranslationLocales()
            ? $this->siteContext->getLocales()
            : $content->getTranslationLocales();

        $staleLocales = [];
        foreach (array_unique($candidateLocales) as $locale) {
            if ($locale === $referenceLocale) {
                continue;
            }

            $translation = $content->getTranslationByLocale($locale);
            if ($translation === null || $translation->getUpdatedAt() < $referenceTranslation->getUpdatedAt()) {
                $staleLocales[] = $locale;
            }
        }

        return $staleLocales;
    }
}
