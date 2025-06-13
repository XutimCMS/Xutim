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

    /**
     * @return array{html: string, cachettl: int}
     * @param  array<string, string>              $options
    */
    public function renderBlock(string $locale, string $code, array $options = []): array
    {
        $block = $this->repo->findByCode($code);
        if ($block === null) {
            return ['html' => '', 'cachettl' => 1];
        }

        if ($this->blockLayoutChecker->checkLayout($block) === false) {
            return [
                'html' => 'The block requirements are not met.',
                'cachettl' => 1
            ];
        }

        $path = $this->layoutLoader->getBlockLayoutTemplate($block->getLayout());
        $layout = $this->layoutLoader->getBlockLayoutByCode($block->getLayout());

        return [
            'html' => $this->localeSwitcher->runWithLocale(
                $locale,
                fn () => $this->twig->render($path, [ 'block' => $block, 'blockOptions' => $options ])
            ),
            'cachettl' => $layout === null ? 0 : 1
        ];
    }
}
