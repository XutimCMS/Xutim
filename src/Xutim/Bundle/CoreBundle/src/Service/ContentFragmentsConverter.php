<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Twig\Environment;

readonly class ContentFragmentsConverter
{
    public function __construct(private Environment $twig)
    {
    }

    /**
     * @param array{time: int, blocks: array{}|array<array{id: string, type: string, data: array<string, mixed>}>, version: string}|array{} $fragments
     */
    public function convertToThemeHtml(array $fragments, string $themePath): string
    {
        if (count($fragments) === 0 || count($fragments['blocks']) === 0) {
            return '';
        }

        return $this->twig->render(sprintf('%s/content_fragment/content.html.twig', $themePath), [
            'fragments' => $fragments,
            'themePath' => $themePath
        ]);
    }

    /**
     * @param array{id: string, type: string, data: array<string, mixed>} $fragment
     */
    public function convertFragmentToThemeHtml(array $fragment, string $themePath): string
    {
        return $this->twig->render(sprintf('%s/content_fragment/content_fragment.html.twig', $themePath), [
            'fragment' => $fragment,
            'themePath' => $themePath
        ]);
    }

    /**
     * @param array{time: int, blocks: array{}|array{id: string, type: string, data: array<string, mixed>}, version: string}|array{} $fragments
     */
    public function convertToAdminHtml(array $fragments): string
    {
        if (count($fragments) === 0 || count($fragments['blocks']) === 0) {
            return '';
        }

        return $this->twig->render('@XutimCore/admin/content_fragment/content.html.twig', [
            'fragments' => $fragments
        ]);
    }

    /**
     * @param array{}|array{
     *     time: int,
     *     blocks: array{}|array<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $fragments
     */
    public function extractIntroduction(array $fragments): string
    {
        if ($fragments === [] || $fragments['blocks'] === []) {
            return '';
        }

        foreach ($fragments['blocks'] as $fragment) {
            if ($fragment['type'] === 'paragraph') {
                /** @var string $text */
                $text = $fragment['data']['text'];

                return $text;
            }
        }

        return '';
    }

    /**
     * @param array{}|array{
     *     time: int,
     *     blocks: array{}|list<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $fragments
     */
    public function extractParagraphs(array $fragments, int $num): string
    {
        if ($fragments === [] || $fragments['blocks'] === []) {
            return '';
        }

        $html = '';
        $count = 0;
        foreach ($fragments['blocks'] as $fragment) {
            if ($fragment['type'] === 'paragraph') {
                /** @var string $paragraph */
                $paragraph = $fragment['data']['text'];
                $html .= sprintf('<p>%s</p>', $paragraph);

                if (++$count === $num) {
                    return $html;
                }
            }
        }

        return $html;
    }

    /**
     * @param array{
     *     time: int,
     *     blocks: array{}|list<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * }|array{} $fragments
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
    public function extractMainImage(array $fragments): array
    {
        if (count($fragments) === 0 || count($fragments['blocks']) === 0) {
            return [];
        }

        $introductionText = '';
        foreach ($fragments['blocks'] as $fragment) {
            if ($fragment['type'] === 'image') {
                /** @var array{ id: string, type:string, data: array{ caption: string, withBorder: bool, withBackground: bool, stretched: bool, file: array{url: string}}} $fragment */
                return $fragment;
            }
        }

        return [];
    }

    /**
     * @param array{
     *     time: int,
     *     blocks: array{}|list<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * }|array{} $fragments
     *
     * @return list<array{
     *     header: string,
     *     paragraph:string,
     *  }>|list{}
     */
    public function extractTimelineElements(array $fragments): array
    {
        if (count($fragments) === 0 || count($fragments['blocks']) === 0) {
            return [];
        }

        $elements = [];
        for ($i = 0; $i < count($fragments['blocks']); $i = $i + 2) {
            /** @var string $header */
            $header = $fragments['blocks'][$i]['data']['text'];
            /** @var string $par */
            $par = $fragments['blocks'][$i + 1]['data']['text'];

            $elements[] = [
                'header' => $header,
                'paragraph' => $par
            ];
        }

        return $elements;
    }
}
