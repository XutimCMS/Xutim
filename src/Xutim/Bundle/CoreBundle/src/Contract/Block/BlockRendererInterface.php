<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Contract\Block;

interface BlockRendererInterface
{
    /**
     * @param array<string, string> $options
     */
    public function renderBlock(string $locale, string $code, array $options = []): RenderedBlock;
}
