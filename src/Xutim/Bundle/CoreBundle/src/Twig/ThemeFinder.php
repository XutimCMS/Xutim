<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig;

use Symfony\Component\Finder\Finder;
use Xutim\CoreBundle\Context\SiteContext;

readonly class ThemeFinder
{
    public function __construct(
        private readonly string $templatesDir,
        private readonly string $themesRelativeDir,
        private readonly SiteContext $siteContext
    ) {
    }

    /**
    * @return array<string>
    */
    public function findAvailableThemes(): array
    {
        $themesPath = sprintf('%s/%s', $this->templatesDir, $this->themesRelativeDir);
        $finder = new Finder();
        $finder->directories()->depth('== 0')->in($themesPath);

        if ($finder->hasResults() === false) {
            return [];
        }

        $dirs = [];
        foreach ($finder as $dir) {
            $dirs[] = $dir->getFilename();
        }

        return $dirs;
    }

    public function getActiveThemePath(string $path = ''): string
    {
        $site = $this->siteContext->getDefaultSite();

        return sprintf('%s/%s%s', $this->themesRelativeDir, $site->theme, $path);
    }
}
