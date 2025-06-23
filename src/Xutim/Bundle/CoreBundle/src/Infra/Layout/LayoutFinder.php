<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Infra\Layout;

use Symfony\Component\Finder\Finder;
use Xutim\CoreBundle\Config\Layout\Layout;
use Xutim\CoreBundle\Config\Layout\LayoutConfig;
use Xutim\CoreBundle\Twig\ThemeFinder;

class LayoutFinder
{
    public function __construct(
        private readonly ThemeFinder $themeFinder,
        private readonly string $templatesDir,
        private readonly string $articleLayoutRelativeDir,
        private readonly string $pageLayoutRelativeDir,
        private readonly string $blockLayoutRelativeDir,
        private readonly string $tagLayoutRelativeDir
    ) {
    }

    /**
     * @return list<Layout>
    */
    public function findArticleLayouts(): array
    {
        return $this->findByPath($this->articleLayoutRelativeDir);
    }

    /**
     * @return list<Layout>
    */
    public function findPageLayouts(): array
    {
        return $this->findByPath($this->pageLayoutRelativeDir);
    }

    /**
     * @return list<Layout>
    */
    public function findBlockLayouts(): array
    {
        return $this->findByPath($this->blockLayoutRelativeDir);
    }

    /**
     * @return list<Layout>
    */
    public function findTagLayouts(): array
    {
        return $this->findByPath($this->tagLayoutRelativeDir);
    }

    /**
     * @return list<Layout>
    */
    private function findByPath(string $path): array
    {
        $themePath = $this->themeFinder->getActiveThemePath();
        $layoutPath = sprintf('%s/%s/%s', $this->templatesDir, $themePath, $path);
        $finder = new Finder();
        $finder->files()->name('config.php')->in($layoutPath);

        $layouts = [];
        foreach ($finder as $file) {
            $layoutConfig = include $file->getRealPath();

            if ($layoutConfig instanceof LayoutConfig) {
                $imageData = null;
                if ($layoutConfig->imagePath !== null) {
                    $imagePath = $file->getPath() . '/' . $layoutConfig->imagePath;
                    $image = file_get_contents($imagePath);
                    if ($image === false) {
                        throw new \RuntimeException(sprintf(
                            'The image "%s" couldn\'t not be loaded.',
                            $imagePath
                        ));
                    }
                    $imageBase64 = base64_encode($image);
                    $imageMimeType = mime_content_type($imagePath);
                    $imageData = "data:$imageMimeType;base64,$imageBase64";
                }
                $layouts[] = new Layout(
                    basename($file->getPath()),
                    $layoutConfig->code,
                    $layoutConfig->name,
                    $imageData,
                    $layoutConfig->config,
                    $layoutConfig->cacheTtl,
                    $layoutConfig->default
                );
            } else {
                throw new \RuntimeException(sprintf(
                    'The file "%s" must return an instance of LayoutConfig.',
                    $file->getRealPath()
                ));
            }
        }

        usort($layouts, function (Layout $l1, Layout $l2) {
            if ($l1->default === true) {
                return -1;
            }
            if ($l2->default === true) {
                return 1;
            }
            
            return strcmp($l1->name, $l2->name);
        });

        return $layouts;
    }
}
