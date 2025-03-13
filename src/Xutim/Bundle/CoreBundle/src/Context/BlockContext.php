<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Context;

use Symfony\Contracts\Cache\CacheInterface;
use Xutim\CoreBundle\Service\BlockRenderer;

class BlockContext
{
    public function __construct(
        private readonly CacheInterface $blockContextCache,
        private readonly SiteContext $siteContext,
        private readonly BlockRenderer $blockRenderer
    ) {
    }

    public function resetBlockTemplate(string $locale, string $code): void
    {
        $this->blockContextCache->delete($this->getCacheKey($locale, $code));
    }

    public function getBlockHtml(string $locale, string $code): string
    {
        $key = $this->getCacheKey($locale, $code);

        return $this->blockContextCache->get(
            $key,
            fn () => $this->blockRenderer->renderBlock($locale, $code)
        );
    }

    public function resetAllLocalesBlockTemplate(string $code): void
    {
        foreach ($this->siteContext->getLocales() as $locale) {
            $this->resetBlockTemplate($locale, $code);
        }
    }

    private function getCacheKey(string $locale, string $code): string
    {
        return sprintf('block_%s_%s', $code, $locale);
    }
}
