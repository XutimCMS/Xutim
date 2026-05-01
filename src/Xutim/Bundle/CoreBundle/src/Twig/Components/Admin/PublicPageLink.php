<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Components\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\TagTranslationInterface;

#[AsTwigComponent(name: 'Xutim:Admin:PublicPageLink')]
final class PublicPageLink
{
    public ?TagTranslationInterface $tagTranslation = null;

    public ?ContentTranslationInterface $contentTranslation = null;

    public function __construct(private readonly SiteContext $siteContext)
    {
    }

    public function hasContentTranslation(): bool
    {
        return $this->contentTranslation !== null;
    }

    public function hasTagTranslation(): bool
    {
        return $this->tagTranslation !== null;
    }

    public function isHomepage(): bool
    {
        if ($this->contentTranslation === null || $this->contentTranslation->hasPage() === false) {
            return false;
        }

        $homepageId = $this->siteContext->getHomepageId();
        if ($homepageId === null) {
            return false;
        }

        return $this->contentTranslation->getPage()->getId()->toRfc4122() === $homepageId;
    }
}
