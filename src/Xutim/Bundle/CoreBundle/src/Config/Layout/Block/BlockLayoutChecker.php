<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block;

use Doctrine\Common\Collections\Collection;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOptionCollection;
use Xutim\CoreBundle\Domain\Model\BlockInterface;
use Xutim\CoreBundle\Domain\Model\BlockItemInterface;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Util\LinkedList;

class BlockLayoutChecker
{
    public function __construct(private readonly LayoutLoader $layoutLoader)
    {
    }

    /**
     * @return list<BlockItemOption>
     */
    public function getLayoutConfig(BlockInterface $block): array
    {
        $layout = $this->layoutLoader->getBlockLayoutByCode($block->getLayout());
        if ($layout === null) {
            return [];
        }

        /** @var list<BlockItemOption> */
        return $layout->config;
    }

    public function checkLayout(BlockInterface $block): bool
    {
        $layout = $this->layoutLoader->getBlockLayoutByCode($block->getLayout());
        if ($layout === null) {
            return true;
        }

        /** @var array<BlockItemOption> $config */
        $config = $layout->config;
        if (count($config) === 0) {
            return true;
        }

        if ($block->getBlockItems()->count() === 0) {
            return false;
        }

        return $this->processItems($block->getBlockItems(), $config);
    }

    /**
     * @param Collection<int, BlockItemInterface> $items
     * @param array<BlockItemOption>              $options
     */
    private function processItems(Collection $items, array $options): bool
    {
        /** @var LinkedList<BlockItemOption> $optionsList */
        $optionsList = new LinkedList($options);

        $activeStates = [0];
        foreach ($items as $item) {
            $newActiveStates = [];
            foreach ($activeStates as $index) {
                $currentOptionNode = $optionsList->getByIndex($index);
                $currentOption = $currentOptionNode->value;
                if ($currentOption->canFullFill($item)) {
                    if ($currentOption instanceof BlockItemOptionCollection) {
                        $newActiveStates[] = $index;
                    }

                    // When fulfilled there is always transition to next option.
                    if ($currentOptionNode->isLast() === false) {
                        $newActiveStates[] = $currentOptionNode->next->index;
                    }

                    if ($item === $items->last() && $currentOptionNode->isLast() === true) {
                        // The last option fulfilled with the last item.
                        return true;
                    }
                }
            }
            $activeStates = array_unique($newActiveStates);

            if (count($activeStates) === 0) {
                // No more valid transitions
                return false;
            }
        }
        $endStates = array_filter($activeStates, fn ($index) => $optionsList->getByIndex($index)->isLast() === true);
        
        return count($endStates) > 0;
    }
}
