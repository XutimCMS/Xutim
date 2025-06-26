<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Repository\TagRepository;

class InternalLinkExtension extends AbstractExtension
{
    public function __construct(
        private readonly UrlGeneratorInterface $router,
        private readonly PageRepository $pageRepo,
        private readonly ArticleRepository $articleRepo,
        private readonly TagRepository $tagRepo,
    ) {
    }
    public function getFilters(): array
    {
        return [
            new TwigFilter('xutim_resolve_internal_links', [$this, 'resolveLinks'], ['is_safe' => ['html']]),
        ];
    }

    public function resolveLinks(string $html, string $locale): string
    {
        $crawler = new Crawler('<div>' . $html . '</div>');
        $pageRepo = $this->pageRepo;
        $articleRepo = $this->articleRepo;
        $tagRepo = $this->tagRepo;

        $crawler->filter('span[data-internal-link-id][data-internal-link-type]')->each(function (Crawler $node) use ($locale, $pageRepo, $articleRepo, $tagRepo) {
            $id = $node->attr('data-internal-link-id');
            $type = $node->attr('data-internal-link-type');
            $text = $node->text();

            $href = null;
            if ($type === 'page') {
                $page = $pageRepo->find($id);
                $trans = $page?->getTranslationByLocale($locale);
                if ($trans !== null) {
                    $href = $this->router->generate('content_translation_show', ['slug' => $trans->getSlug()]);
                }
            }

            if ($type === 'article') {
                $article = $articleRepo->find($id);
                $trans = $article?->getTranslationByLocale($locale);
                if ($trans !== null) {
                    $href = $this->router->generate('content_translation_show', ['slug' => $trans->getSlug()]);
                }
            }

            if ($type === 'tag') {
                $tag = $tagRepo->find($id);
                $trans = $tag?->getTranslationByLocale($locale);
                if ($trans !== null) {
                    $href = $this->router->generate('tag_translation_show', ['slug' => $trans->getSlug()]);
                }
            }

            if ($href !== null) {
                $domNode = $node->getNode(0);
                if ($domNode instanceof \DOMNode && $domNode->ownerDocument instanceof \DOMDocument && $domNode->parentNode instanceof \DOMNode) {
                    $doc = $domNode->ownerDocument;
                    $fragment = $doc->createDocumentFragment();
                    $fragment->appendXML(sprintf(
                        '<a href="%s">%s</a>',
                        htmlspecialchars($href, ENT_QUOTES),
                        htmlspecialchars($text)
                    ));

                    $domNode->parentNode->replaceChild($fragment, $domNode);
                }
            }
        });

        $content = '';
        $rootNode = $crawler->getNode(0);
        if ($rootNode instanceof \DOMNode && $rootNode->ownerDocument instanceof \DOMDocument) {
            foreach ($rootNode->childNodes as $child) {
                $content .= $rootNode->ownerDocument->saveHTML($child);
            }
        }

        return $content;
    }
}
