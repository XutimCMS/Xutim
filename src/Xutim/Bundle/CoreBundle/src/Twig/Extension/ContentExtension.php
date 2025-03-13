<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\PageRepository;

class ContentExtension extends AbstractExtension
{
    public function __construct(
        private readonly ArticleRepository $articleRepo,
        private readonly PageRepository $pageRepo,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_page', [$this, 'findPage']),
            new TwigFunction('get_article', [$this, 'findArticle']),
        ];
    }

    public function findPage(string $id): ?Page
    {
        return $this->pageRepo->find($id);
    }

    public function findArticle(string $id): ?Article
    {
        return $this->articleRepo->find($id);
    }
}
