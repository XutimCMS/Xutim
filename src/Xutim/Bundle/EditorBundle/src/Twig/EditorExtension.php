<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\EditorBundle\Domain\Model\ContentBlockInterface;
use Xutim\EditorBundle\Domain\Template\BlockTemplateInterface;
use Xutim\EditorBundle\Domain\Template\BlockTemplateRegistry;
use Xutim\EditorBundle\Entity\Block\LayoutBlock;
use Xutim\EditorBundle\Repository\ContentBlockRepository;

final class EditorExtension extends AbstractExtension
{
    public function __construct(
        private readonly BlockTemplateRegistry $templateRegistry,
        private readonly ContentBlockRepository $blockRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('editor_block_template', $this->getBlockTemplate(...)),
            new TwigFunction('editor_block_templates', $this->getAllBlockTemplates(...)),
            new TwigFunction('editor_block_slots', $this->getBlockSlots(...)),
        ];
    }

    public function getBlockTemplate(string $name): ?BlockTemplateInterface
    {
        return $this->templateRegistry->get($name);
    }

    /**
     * @return array<string, BlockTemplateInterface>
     */
    public function getAllBlockTemplates(): array
    {
        return $this->templateRegistry->all();
    }

    /**
     * @return array<int, list<ContentBlockInterface>>
     */
    public function getBlockSlots(LayoutBlock $layoutBlock): array
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
