<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\UuidV4;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;

class InternalLinkExtension extends AbstractExtension
{
    public function __construct(
        private readonly UrlGeneratorInterface $router,
        private readonly PageRepository $pageRepo,
        private readonly ArticleRepository $articleRepo,
        private readonly TagRepository $tagRepo,
        private readonly MediaRepositoryInterface $mediaRepo,
        private readonly RequestStack $requestStack
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
        $requestLocale = $this->requestStack->getMainRequest()?->getLocale() ?? '';

        $crawler->filter('span[data-internal-link-id][data-internal-link-type]')->each(function (Crawler $node) use ($locale, $pageRepo, $articleRepo, $tagRepo, $requestLocale) {
            $id = $node->attr('data-internal-link-id');
            $type = $node->attr('data-internal-link-type');
            $text = $node->text();

            $href = null;
            if ($type === 'page') {
                $page = $pageRepo->find($id);
                $trans = $page?->getTranslationByLocaleOrDefault($locale);
                if ($trans !== null) {
                    $params = ['slug' => $trans->getSlug()];
                    if ($requestLocale !== $trans->getLocale()) {
                        $params['_content_locale'] = $trans->getLocale();
                    }
                    $href = $this->router->generate('content_translation_show', $params);
                }
            }

            if ($type === 'article') {
                $article = $articleRepo->find($id);
                $trans = $article?->getTranslationByLocaleOrDefault($locale);
                if ($trans !== null) {
                    $params = ['slug' => $trans->getSlug()];
                    if ($requestLocale !== $trans->getLocale()) {
                        $params['_content_locale'] = $trans->getLocale();
                    }
                    $href = $this->router->generate('content_translation_show', $params);
                }
            }

            if ($type === 'tag') {
                $tag = $tagRepo->find($id);
                $trans = $tag?->getTranslationByLocaleOrAny($locale);
                if ($trans !== null) {
                    $params = ['slug' => $trans->getSlug()];
                    if ($requestLocale !== $trans->getLocale()) {
                        $params['_content_locale'] = $trans->getLocale();
                    }
                    $href = $this->router->generate('tag_translation_show', $params);
                }
            }

            if ($type === 'file') {
                $media = $this->mediaRepo->findById(new UuidV4($id));
                if ($media !== null) {
                    $href = $this->router->generate('file_show', ['id' => $media->id(), 'extension' => $media->originalExt()]);
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
