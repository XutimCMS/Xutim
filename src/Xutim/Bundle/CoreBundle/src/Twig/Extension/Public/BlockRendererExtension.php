<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension\Public;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\CoreBundle\Context\BlockContext;

class BlockRendererExtension extends AbstractExtension
{
    public function __construct(
        private readonly BlockContext $blockContext,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('block_render', [$this, 'renderBlock'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param array<string, string> $options
    */
    public function renderBlock(string $code, string $locale, array $options = []): string
    {
        return $this->blockContext->getBlockHtml($locale, $code, $options);
    }
}
