<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Context;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\BlockInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Domain\Model\SnippetInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Repository\BlockRepository;
use Xutim\CoreBundle\Service\BlockRenderer;

class BlockContext
{
    public function __construct(
        private readonly CacheInterface $blockContextCache,
        private readonly SiteContext $siteContext,
        private readonly BlockRenderer $blockRenderer,
        private readonly BlockRepository $blockRepository
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
                $ret = $blockRenderer->renderBlock($locale, $code, $options);
                $item->expiresAfter($ret['cachettl']);

                return $ret['html'];
            }
        );
    }

    public function resetAllLocalesBlockTemplate(string $code): void
    {
        foreach ($this->siteContext->getLocales() as $locale) {
            $this->resetBlockTemplate($locale, $code);
        }
    }

    public function resetBlocksBelongsToArticle(ArticleInterface $article): void
    {
        $blocks = $this->blockRepository->findByArticle($article);
        $this->resetBlocks($blocks);
    }

    public function resetBlocksBelongsToSnippet(SnippetInterface $snippet): void
    {
        $blocks = $this->blockRepository->findBySnippet($snippet);
        $this->resetBlocks($blocks);
    }

    public function resetBlocksBelongsToFile(FileInterface $file): void
    {
        $blocks = $this->blockRepository->findByFile($file);
        $this->resetBlocks($blocks);
    }

    public function resetBlocksBelongsToContentTranslation(ContentTranslationInterface $trans): void
    {
        if ($trans->hasPage() === true) {
            $this->resetBlocksBelongsToPage($trans->getPage());
        }
        if ($trans->hasArticle() === true) {
            $this->resetBlocksBelongsToArticle($trans->getArticle());
        }
    }

    public function resetBlocksBelongsToTag(TagInterface $tag): void
    {
        $blocks = $this->blockRepository->findByTag($tag);
        $this->resetBlocks($blocks);
    }

    public function resetBlocksBelongsToPage(PageInterface $page): void
    {
        $blocks = $this->blockRepository->findByPage($page);
        $this->resetBlocks($blocks);
    }

    /**
     * @param array<BlockInterface> $blocks
    */
    private function resetBlocks(array $blocks): void
    {
        foreach ($blocks as $block) {
            $this->resetAllLocalesBlockTemplate($block->getCode());
        }
    }

    private function getCacheKey(string $locale, string $code): string
    {
        return sprintf('block_%s_%s', $code, $locale);
    }
}
