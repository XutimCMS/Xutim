<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Xutim\CoreBundle\Content\CanonicalContentExtractor;
use Xutim\CoreBundle\Content\Transform\EditorJsToCanonicalDocumentTransformer;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Repository\BlockRepository;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;

readonly class SearchContentBuilder
{
    public function __construct(
        private readonly BlockRepository $blockRepo,
        private readonly EditorJsToCanonicalDocumentTransformer $transformer,
        private readonly CanonicalContentExtractor $extractor,
    ) {
    }

    public function build(ContentTranslationInterface $trans): string
    {
        $locale = $trans->getLocale();

        $parts = [];

        $document = $this->transformer->transform($trans->getContent());
        foreach ($this->extractor->flattenBlocks($document->blocks) as $block) {
            switch ($block->kind) {
                case 'paragraph':
                case 'heading':
                    $parts[] = $this->extractor->runsToPlainText($block->body);
                    break;
                case 'hero_heading':
                    $parts[] = $this->extractor->runsToPlainText($block->parts['pretitle'] ?? []);
                    $parts[] = $this->extractor->runsToPlainText($block->parts['title'] ?? []);
                    $parts[] = $this->extractor->runsToPlainText($block->parts['subtitle'] ?? []);
                    break;
                case 'quote':
                    $parts[] = $this->extractor->runsToPlainText($block->parts['body'] ?? []);
                    $parts[] = $this->extractor->runsToPlainText($block->parts['caption'] ?? []);
                    break;
                case 'list':
                    foreach ($block->items as $item) {
                        $parts[] = $this->extractor->runsToPlainText($item['body'] ?? []);
                    }
                    break;
                case 'snippet':
                    $parts = array_merge($parts, $this->extractBlockContent((string) ($block->attrs['code'] ?? ''), $locale));
                    break;
            }
        }

        return implode(' ', array_filter($parts, fn ($val) => $val !== ''));
    }

    public function buildTagContent(ContentTranslationInterface $trans): string
    {
        $parts = [];
        if ($trans->hasArticle() === true) {
            foreach ($trans->getArticle()->getTags() as $tag) {
                $tagTrans = $tag->getTranslationByLocale($trans->getLocale());
                if ($tagTrans !== null) {
                    $parts[] = $tagTrans->getName();
                }
            }
        }

        return implode(' ', array_filter($parts, fn ($val) => $val !== ''));
    }

    /**
     * @return list<string>
    */
    private function extractBlockContent(string $code, string $locale): array
    {
        $parts = [];
        $block = $this->blockRepo->findOneBy(['code' => $code]);
        if ($block !== null) {
            foreach ($block->getBlockItems() as $item) {
                if ($item->hasSnippet() === true) {
                    /** @var SnippetInterface $snippet */
                    $snippet = $item->getSnippet();
                    $trans = $snippet->getTranslationByLocale($locale);
                    if ($trans !== null) {
                        $parts[] = $trans->getContent();
                    }
                }
            }
        }

        return $parts;
    }
}
