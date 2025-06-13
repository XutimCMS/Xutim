<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;
use Xutim\CoreBundle\Domain\Model\BlockInterface;
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

        return array_map(fn (BlockInterface $block) => [
            'code' => $block->getCode(),
            'label' => $block->getName()
        ], $blocks);
    }

    public function fetchBlock(string $code): ?BlockInterface
    {
        $block = $this->blockRepository->findByCode($code);
        if ($block === null) {
            return null;
        }

        return $block;
    }
}
