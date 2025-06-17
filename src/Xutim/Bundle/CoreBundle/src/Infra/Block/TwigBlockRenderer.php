<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Infra\Block;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\LocaleSwitcher;
use Twig\Environment;
use Xutim\CoreBundle\Config\Layout\Block\BlockLayoutChecker;
use Xutim\CoreBundle\Contract\Block\BlockRendererInterface;
use Xutim\CoreBundle\Contract\Block\RenderedBlock;
use Xutim\CoreBundle\Domain\Model\UserInterface;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Repository\BlockRepository;

final class TwigBlockRenderer implements BlockRendererInterface
{
    public function __construct(
        private readonly BlockRepository $repo,
        private readonly Environment $twig,
        private readonly LayoutLoader $layoutLoader,
        private readonly BlockLayoutChecker $blockLayoutChecker,
        private readonly LocaleSwitcher $localeSwitcher,
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * @param array<string, string> $options
     */
    public function renderBlock(string $locale, string $code, array $options = []): RenderedBlock
    {
        $block = $this->repo->findByCode($code);
        if ($block === null) {
            return new RenderedBlock('', 1);
        }

        if ($this->blockLayoutChecker->checkLayout($block) === false) {
            if ($this->authChecker->isGranted(UserInterface::ROLE_USER) === false) {
                return new RenderedBlock('', 1);
            }
            $requirementsHtml = sprintf('The block requirements are not met for <a href="%s">%s</a>', $this->urlGenerator->generate('admin_block_show', ['id' => $block->getId()]), $code);

            return new RenderedBlock($requirementsHtml, 1);
        }

        $path = $this->layoutLoader->getBlockLayoutTemplate($block->getLayout());
        $layout = $this->layoutLoader->getBlockLayoutByCode($block->getLayout());


        return new RenderedBlock($this->localeSwitcher->runWithLocale(
            $locale,
            fn () => $this->twig->render($path, [ 'block' => $block, 'blockOptions' => $options ])
        ), $layout === null ? 0 : 1);
    }
}
