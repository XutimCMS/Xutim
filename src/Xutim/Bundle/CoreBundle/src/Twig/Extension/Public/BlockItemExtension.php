<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension\Public;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Xutim\CoreBundle\Domain\Model\BlockItemInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;

class BlockItemExtension extends AbstractExtension
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly RequestStack $reqStack
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('block_item_link', [$this, 'getLink'], ['is_safe' => ['html']]),
            new TwigFilter('block_item_translation', [$this, 'getTranslation'], ['is_safe' => ['html']]),
        ];
    }

    public function getTranslation(BlockItemInterface $item): ContentTranslationInterface|null
    {
        $locale = $this->reqStack->getCurrentRequest()?->getLocale();

        if ($locale !== null && $item->hasContentObject() === true) {
            return $item->getObject()?->getTranslationByLocale($locale);
        }

        return null;
    }

    public function getLink(BlockItemInterface $item): string
    {
        if ($item->isSimpleItem() === true) {
            return $item->getLink() ?? '';
        }

        // Overrides the article/page path.
        if ($item->getLink() !== null && $item->getLink() !== '') {
            return $item->getLink();
        }

        return $this->router->generate('content_translation_show', [
            'slug' => $this->getTranslation($item)?->getSlug() ?? ''
        ]);
    }
}
