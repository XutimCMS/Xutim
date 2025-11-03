<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Xutim\CoreBundle\Service\ContentFragmentsConverter;
use Xutim\CoreBundle\Twig\ThemeFinder;

class ContentFragmentsExtension extends AbstractExtension
{
    public function __construct(
        private readonly ContentFragmentsConverter $fragmentConverter,
        private readonly ThemeFinder $themeFinder
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_content_fragment', [$this, 'fragmentToHtml'], ['is_safe' => ['html']]),
            new TwigFunction('render_content_fragments', [$this, 'fragmentsToHtml'], ['is_safe' => ['html']]),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('content_fragments_to_theme_html', [$this, 'toThemeHtml'], ['is_safe' => ['html']]),
            new TwigFilter('content_fragments_to_admin_html', [$this, 'toAdminHtml'], ['is_safe' => ['html']]),
            new TwigFilter('content_fragments_extract_introduction', [$this, 'toIntroductionHtml'], ['is_safe' => ['html']]),
            new TwigFilter('content_fragments_extract_paragraphs', [$this, 'paragraphsToHtml'], ['is_safe' => ['html']]),
            new TwigFilter('content_fragments_extract_copyrights', [$this, 'extractCopyrights'], ['is_safe' => ['html']]),
            new TwigFilter('content_fragments_extract_timeline_elements', [$this, 'toTimelineElements'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param array{id: string, type: string, data: array<string, mixed>} $fragment
     * @param array<string, string>                                       $options
     */
    public function fragmentToHtml(array $fragment, string $locale, array $options = []): string
    {
        $path = $this->themeFinder->getActiveThemePath();

        return $this->fragmentConverter->convertFragmentToThemeHtml($fragment, $path, $locale, $options);
    }

    /**
     * @param EditorBlock           $fragments
     * @param array<string, string> $locale
     */
    public function fragmentsToHtml(array $fragments, string $locale, array $options = []): string
    {
        $path = $this->themeFinder->getActiveThemePath();

        return $this->fragmentConverter->convertFragmentsToThemeHtml($fragments, $path, $locale, $options);
    }

    /**
     * @param EditorBlock $fragments
     */
    public function toThemeHtml(array $fragments, string $locale): string
    {
        $path = $this->themeFinder->getActiveThemePath();

        return $this->fragmentConverter->convertToThemeHtml($fragments, $path, $locale);
    }

    /**
     * @param EditorBlock $fragments
     */
    public function toAdminHtml(array $fragments, string $locale): string
    {
        return $this->fragmentConverter->convertToAdminHtml($fragments, $locale);
    }

    /**
     * @param EditorBlock $fragments
     */
    public function toIntroductionHtml(array $fragments): string
    {
        return $this->fragmentConverter->extractIntroduction($fragments);
    }
    
    /**
     * @param EditorBlock $fragments
     */
    public function paragraphsToHtml(array $fragments, int $num = 1): string
    {
        return $this->fragmentConverter->extractParagraphs($fragments, $num);
    }

    /**
     * @param EditorBlock $fragments
     *
     * @return array<string, string>
     */
    public function extractCopyrights(array $fragments): array
    {
        return $this->fragmentConverter->extractCopyrights($fragments);
    }

    /**
     * @param EditorBlock $fragments
     *
     * @return list<array{
     *     header: string,
     *     paragraph:string,
     *  }>|list{}
     */
    public function toTimelineElements(array $fragments): array
    {
        return $this->fragmentConverter->extractTimelineElements($fragments);
    }
}
