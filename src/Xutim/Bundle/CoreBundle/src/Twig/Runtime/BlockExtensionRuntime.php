<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;
use Xutim\CoreBundle\Entity\Block;
use Xutim\CoreBundle\Repository\BlockRepository;

class BlockExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly BlockRepository $blockRepository)
    {
    }

    /**
    * @return array<array{code: string, label: string}>
    */
    public function fetchCodes(): array
    {
        $blocks = $this->blockRepository->findAll();

        return array_map(fn (Block $block) => [
            'code' => $block->getCode(),
            'label' => $block->getName()
        ], $blocks);
    }
}
