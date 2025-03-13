<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Symfony\Component\Translation\LocaleSwitcher;
use Twig\Environment;
use Xutim\CoreBundle\Config\Layout\Block\BlockLayoutChecker;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Repository\BlockRepository;

class BlockRenderer
{
    public function __construct(
        private readonly BlockRepository $repo,
        private readonly Environment $twig,
        private readonly LayoutLoader $layoutLoader,
        private readonly BlockLayoutChecker $blockLayoutChecker,
        private readonly LocaleSwitcher $localeSwitcher
    ) {
    }

    public function renderBlock(string $locale, string $code): string
    {
        $block = $this->repo->findByCode($code);
        if ($block === null) {
            return '';
        }

        if ($this->blockLayoutChecker->checkLayout($block) === false) {
            return 'The block requirements are not met.';
        }

        $path = $this->layoutLoader->getBlockLayoutTemplate($block->getLayout());

        return $this->localeSwitcher->runWithLocale(
            $locale,
            fn () => $this->twig->render($path, [ 'block' => $block ])
        );
    }
}
