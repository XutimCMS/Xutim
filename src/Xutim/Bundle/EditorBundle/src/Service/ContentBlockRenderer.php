<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Service;

use Twig\Environment;
use Xutim\EditorBundle\Domain\Model\ContentBlockInterface;
use Xutim\EditorBundle\Domain\Model\ContentDraftInterface;
use Xutim\EditorBundle\Domain\Template\BlockTemplateRegistry;
use Xutim\EditorBundle\Entity\Block\LayoutBlock;
use Xutim\EditorBundle\Repository\ContentBlockRepository;

class ContentBlockRenderer
{
    public function __construct(
        private readonly Environment $twig,
        private readonly BlockTemplateRegistry $templateRegistry,
        private readonly ContentBlockRepository $blockRepository,
    ) {
    }

    public function renderBlock(ContentBlockInterface $block, string $themePath, string $locale): string
    {
        $template = sprintf('%s/content_block/%s.html.twig', $themePath, $block->getType());

        $context = [
            'block' => $block,
            'themePath' => $themePath,
            'locale' => $locale,
        ];

        if ($block instanceof LayoutBlock) {
            $context['slots'] = $this->getSlotContents($block);
            $context['template'] = $this->templateRegistry->get($block->getTemplate());
        }

        return $this->twig->render($template, $context);
    }

    public function renderDraft(ContentDraftInterface $draft, string $themePath, string $locale): string
    {
        $blocks = $this->blockRepository->findByDraft($draft);

        return $this->twig->render(sprintf('%s/content_block/content.html.twig', $themePath), [
            'blocks' => $blocks,
            'themePath' => $themePath,
            'locale' => $locale,
        ]);
    }

    public function renderDraftAdmin(ContentDraftInterface $draft, string $locale): string
    {
        $blocks = $this->blockRepository->findByDraft($draft);

        return $this->twig->render('@XutimEditor/admin/content_block/content.html.twig', [
            'blocks' => $blocks,
            'locale' => $locale,
        ]);
    }

    public function renderBlockAdmin(ContentBlockInterface $block, string $locale): string
    {
        $template = sprintf('@XutimEditor/admin/content_block/%s.html.twig', $block->getType());

        $context = [
            'block' => $block,
            'locale' => $locale,
        ];

        if ($block instanceof LayoutBlock) {
            $context['slots'] = $this->getSlotContents($block);
            $context['template'] = $this->templateRegistry->get($block->getTemplate());
        }

        return $this->twig->render($template, $context);
    }

    /**
     * @return array<int, list<ContentBlockInterface>>
     */
    private function getSlotContents(LayoutBlock $layoutBlock): array
    {
        $children = $this->blockRepository->findByParent($layoutBlock);
        $slots = [];

        foreach ($children as $child) {
            $slot = $child->getSlot() ?? 0;
            if (!isset($slots[$slot])) {
                $slots[$slot] = [];
            }
            $slots[$slot][] = $child;
        }

        ksort($slots);

        return $slots;
    }
}
