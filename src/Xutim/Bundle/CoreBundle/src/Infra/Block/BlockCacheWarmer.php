<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Infra\Block;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Repository\BlockRepository;

class BlockCacheWarmer implements CacheWarmerInterface
{
    public function __construct(
        private readonly BlockRepository $blockRepository,
        private readonly BlockContext $blockContext,
        private readonly SiteContext $siteContext,
    ) {
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        $locales = $this->siteContext->getLocales();
        $blocks = $this->blockRepository->findAll();

        foreach ($blocks as $block) {
            $code = $block->getCode();
            foreach ($locales as $locale) {
                $this->blockContext->resetBlockTemplate($locale, $code);
                $this->blockContext->getBlockHtml($locale, $code);
            }
        }

        return [];
    }

    public function isOptional(): bool
    {
        return true;
    }
}
