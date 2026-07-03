<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\LocaleAwareInterface;
use Xutim\CoreBundle\Domain\Model\PublishableTranslationInterface;
use Xutim\CoreBundle\Domain\Model\TranslatableInterface;
use Xutim\CoreBundle\Service\ReferenceTranslationResolver;

class ContentLocaleExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly SiteContext $siteContext,
        private readonly ReferenceTranslationResolver $referenceTranslationResolver,
    ) {
    }

    public function isExtendedLocale(string $locale): bool
    {
        $found = array_find(
            $this->siteContext->getExtendedContentLocales(),
            fn (string $needle) => $locale === $needle
        );
        if ($found === null) {
            return false;
        }

        return true;
    }

    /**
     * Lenient Twig wrapper around {@see ReferenceTranslationResolver::resolveByLocale()}:
     * returns null when the entity is null or has no translations, so templates can use
     * the result with `{% if %}` guards without each call needing a `entity ? ... : null` wrap.
     *
     * @param TranslatableInterface<LocaleAwareInterface>|null $entity
     */
    public function resolveTranslation(?TranslatableInterface $entity, string $locale): ?LocaleAwareInterface
    {
        if ($entity === null) {
            return null;
        }
        foreach ($entity->getTranslations() as $first) {
            return $this->referenceTranslationResolver->resolveByLocale($entity, $locale);
        }

        return null;
    }

    /**
     * Lenient Twig wrapper around {@see ReferenceTranslationResolver::resolvePublishedByLocale()}:
     * returns null when the entity is null or has no published translation in any locale, so
     * public templates can guard with `{% if %}` and never link to unpublished content.
     *
     * @param TranslatableInterface<LocaleAwareInterface&PublishableTranslationInterface>|null $entity
     */
    public function resolvePublishedTranslation(?TranslatableInterface $entity, string $locale): ?LocaleAwareInterface
    {
        if ($entity === null) {
            return null;
        }

        return $this->referenceTranslationResolver->resolvePublishedByLocale($entity, $locale);
    }
}
