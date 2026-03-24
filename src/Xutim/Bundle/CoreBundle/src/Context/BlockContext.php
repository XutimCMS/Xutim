<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Context;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Xutim\CoreBundle\Cache\SnippetUsageTracker;
use Xutim\CoreBundle\Contract\Block\BlockRendererInterface;
use Xutim\CoreBundle\Repository\BlockRepository;

class BlockContext
{
    public function __construct(
        private readonly TagAwareCacheInterface $blockContextCache,
        private readonly BlockRendererInterface $blockRenderer,
        private readonly BlockRepository $blockRepository,
        private readonly SnippetUsageTracker $snippetUsageTracker
    ) {
    }

    public function resetBlockTemplate(string $locale, string $code): void
    {
        $this->blockContextCache->delete($this->getCacheKey($locale, $code));
    }

    /**
     * @param array<string, string> $options
    */
    public function getBlockHtml(string $locale, string $code, array $options = []): string
    {
        $key = $this->getCacheKey($locale, $code);
        $blockRenderer = $this->blockRenderer;

        return $this->blockContextCache->get(
            $key,
            function (ItemInterface $item) use ($blockRenderer, $locale, $code, $options) {
                $this->snippetUsageTracker->push();
                $ret = $blockRenderer->renderBlock($locale, $code, $options);
                $snippetCodes = $this->snippetUsageTracker->pop();

                $item->expiresAfter($ret->cacheTtl);
                $item->tag($this->buildTagsForBlock($code, $snippetCodes));

                return $ret->html;
            }
        );
    }

    public function resetAllLocalesBlockTemplate(string $code): void
    {
        $this->blockContextCache->invalidateTags([$this->blockTag($code)]);
    }

    public function blockTag(string $code): string
    {
        return 'block.' . $code;
    }

    /**
     * @param list<string> $snippetCodes
     * @return list<string>
     */
    private function buildTagsForBlock(string $code, array $snippetCodes): array
    {
        $tags = [$this->blockTag($code)];

        foreach ($snippetCodes as $snippetCode) {
            $tags[] = 'snippet.' . $snippetCode;
        }

        $block = $this->blockRepository->findByCode($code);
        if ($block === null) {
            return $tags;
        }

        foreach ($block->getBlockItems() as $item) {
            if ($item->hasArticle()) {
                $tags[] = 'article.' . $item->getArticle()->getId();
            }
            if ($item->hasPage()) {
                $tags[] = 'page.' . $item->getPage()->getId();
            }
            if ($item->hasTag()) {
                $tags[] = 'tag.' . $item->getTag()->getId();
            }
            if ($item->hasFile()) {
                $tags[] = 'media.' . $item->getFile()->id();
            }
            if ($item->hasSnippet()) {
                $tags[] = 'snippet.' . $item->getSnippet()->getCode();
            }
            if ($item->hasMediaFolder()) {
                $tags[] = 'mediafolder.' . $item->getMediaFolder()->id();
            }
        }

        return array_values(array_unique($tags));
    }

    private function getCacheKey(string $locale, string $code): string
    {
        return sprintf('block_%s_%s', $code, $locale);
    }
}
