<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Xutim\EditorBundle\Entity\Block\CodeBlock;
use Xutim\EditorBundle\Entity\Block\EmbedBlock;
use Xutim\EditorBundle\Entity\Block\HeadingBlock;
use Xutim\EditorBundle\Entity\Block\ImageBlock;
use Xutim\EditorBundle\Entity\Block\LayoutBlock;
use Xutim\EditorBundle\Entity\Block\ListItemBlock;
use Xutim\EditorBundle\Entity\Block\ParagraphBlock;
use Xutim\EditorBundle\Entity\Block\QuoteBlock;

class ContentBlockDiscriminatorMapPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $defaultBlockTypes = [
            ParagraphBlock::TYPE => ParagraphBlock::class,
            HeadingBlock::TYPE => HeadingBlock::class,
            ListItemBlock::TYPE => ListItemBlock::class,
            QuoteBlock::TYPE => QuoteBlock::class,
            ImageBlock::TYPE => ImageBlock::class,
            EmbedBlock::TYPE => EmbedBlock::class,
            CodeBlock::TYPE => CodeBlock::class,
            LayoutBlock::TYPE => LayoutBlock::class,
        ];

        /** @var array<string, class-string> $customBlockTypes */
        $customBlockTypes = $container->hasParameter('xutim_editor.block_types')
            ? $container->getParameter('xutim_editor.block_types')
            : [];

        $allBlockTypes = array_merge($defaultBlockTypes, $customBlockTypes);

        $container->setParameter('xutim_editor.block_types', $allBlockTypes);
    }
}
