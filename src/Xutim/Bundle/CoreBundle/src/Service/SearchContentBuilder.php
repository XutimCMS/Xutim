<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Repository\BlockRepository;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;

readonly class SearchContentBuilder
{
    public function __construct(
        private readonly BlockRepository $blockRepo,
    ) {
    }

    public function build(ContentTranslationInterface $trans): string
    {
        $locale = $trans->getLocale();

        $parts = [];

        $data = $trans->getContent();
        if (isset($data['blocks'])) {
            foreach ($data['blocks'] as $block) {
                switch ($block['type']) {
                    case 'paragraph':
                    case 'header':
                        $parts[] = $block['data']['text'];
                        break;
                    case 'mainHeader':
                        $parts[] = $block['data']['pretitle'];
                        $parts[] = $block['data']['title'];
                        $parts[] = $block['data']['subtitle'];
                        break;
                    case 'quote':
                        $parts[] = $block['data']['text'];
                        $parts[] = $block['data']['caption'];
                        break;
                    case 'list':
                        foreach ($block['data']['items'] as $item) {
                            $parts[] = $item['content'];
                        }
                        break;
                    case 'block':
                        $parts = array_merge($parts, $this->extractBlockContent($block['data']['code'], $locale));
                }
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
