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
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('content_fragments_to_theme_html', [$this, 'toThemeHtml'], ['is_safe' => ['html']]),
            new TwigFilter('content_fragments_to_admin_html', [$this, 'toAdminHtml'], ['is_safe' => ['html']]),
            new TwigFilter('content_fragments_extract_introduction', [$this, 'toIntroductionHtml'], ['is_safe' => ['html']]),
            new TwigFilter('content_fragments_extract_paragraphs', [$this, 'paragraphsToHtml'], ['is_safe' => ['html']]),
            new TwigFilter('content_fragments_extract_image', [$this, 'toImageHtml'], ['is_safe' => ['html']]),
            new TwigFilter('content_fragments_extract_timeline_elements', [$this, 'toTimelineElements'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param array{id: string, type: string, data: array<string, mixed>} $fragment
     */
    public function fragmentToHtml(array $fragment): string
    {
        $path = $this->themeFinder->getActiveThemePath();

        return $this->fragmentConverter->convertFragmentToThemeHtml($fragment, $path);
    }

    /**
     * @param array{}|array{
     *     time: int,
     *     blocks: array{}|list<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $fragments
     */
    public function toThemeHtml(array $fragments): string
    {
        $path = $this->themeFinder->getActiveThemePath();

        return $this->fragmentConverter->convertToThemeHtml($fragments, $path);
    }

    /**
     * @param array{}|array{
     *     time: int,
     *     blocks: array{}|list<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $fragments
     */
    public function toAdminHtml(array $fragments): string
    {
        return $this->fragmentConverter->convertToAdminHtml($fragments);
    }

    /**
     * @param array{}|array{
     *     time: int,
     *     blocks: array{}|list<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $fragments
     */
    public function toIntroductionHtml(array $fragments): string
    {
        return $this->fragmentConverter->extractIntroduction($fragments);
    }
    
    /**
     * @param array{}|array{
     *     time: int,
     *     blocks: array{}|list<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $fragments
     */
    public function paragraphsToHtml(array $fragments, int $num = 1): string
    {
        return $this->fragmentConverter->extractParagraphs($fragments, $num);
    }

    /**
     * @param array{}|array{
     *     time: int,
     *     blocks: array{}|list<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $fragments
     *
     * @return array{
     *     id: string,
     *     type:string,
     *     data: array{
     *         caption: string,
     *         withBorder: bool,
     *         withBackground: bool,
     *         stretched: bool,
     *         file: array{url: string}
     *      }
     *  }|array{}
     */
    public function toImageHtml(array $fragments): array
    {
        return $this->fragmentConverter->extractMainImage($fragments);
    }

    /**
     * @param array{}|array{
     *     time: int,
     *     blocks: array{}|list<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $fragments
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
