<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Symfony\Component\HttpFoundation\Exception\UnexpectedValueException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Xutim\CoreBundle\Repository\FileRepository;

readonly class ContentFragmentsConverter
{
    public function __construct(
        private readonly Environment $twig,
        private readonly FileRepository $fileRepo
    ) {
    }

    /**
     * @param EditorBlock $fragments
     */
    public function convertToThemeHtml(array $fragments, string $themePath, string $locale): string
    {
        if (count($fragments) === 0 || count($fragments['blocks']) === 0) {
            return '';
        }

        return $this->twig->render(sprintf('%s/content_fragment/content.html.twig', $themePath), [
            'fragments' => $fragments,
            'themePath' => $themePath,
            'locale' => $locale
        ]);
    }

    /**
     * @param array{id: string, type: string, data: array<string, mixed>} $fragment
     * @param array<string, string>                                       $options
     */
    public function convertFragmentToThemeHtml(
        array $fragment,
        string $themePath,
        string $locale,
        array $options = []
    ): string {
        return $this->twig->render(sprintf('%s/content_fragment/content_fragment.html.twig', $themePath), [
            'fragment' => $fragment,
            'themePath' => $themePath,
            'fragmentOptions' => $options,
            'locale' => $locale
        ]);
    }

    /**
     * @param EditorBlock $fragments
     */
    public function convertToAdminHtml(array $fragments, string $locale): string
    {
        if (count($fragments) === 0 || count($fragments['blocks']) === 0) {
            return '';
        }

        return $this->twig->render('@XutimCore/admin/content_fragment/content.html.twig', [
            'fragments' => $fragments,
            'locale' => $locale
        ]);
    }

    /**
     * @param EditorBlock $fragments
     */
    public function extractIntroduction(array $fragments): string
    {
        if ($fragments === [] || $fragments['blocks'] === []) {
            return '';
        }

        $text = '';
        foreach ($fragments['blocks'] as $fragment) {
            if ($fragment['type'] === 'paragraph' || $fragment['type'] === 'quote') {
                /** @var string $par */
                $par = $fragment['data']['text'];
                $text .= $par . ' ';
                if (strlen($text) > 500) {
                    return $text;
                }
            }
            if ($fragment['type'] === 'list') {
                foreach ($fragment['data']['items'] as $item) {
                    /** @var string $par */
                    $par = $item['content'];
                    $text .= $par . ' ';
                    if (strlen($text) > 500) {
                        return $text;
                    }
                }
            }
        }

        return $text;
    }

    /**
     * @param EditorBlock $fragments
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
     * @param EditorBlock $fragments
     *
     * @return array<string, string>
     */
    public function extractCopyrights(array $fragments): array
    {
        if (count($fragments) === 0 || count($fragments['blocks']) === 0) {
            return [];
        }

        $copyrights = [];
        foreach ($fragments['blocks'] as $fragment) {
            if ($fragment['type'] === 'xutimImage') {
                $file = $this->fileRepo->find($fragment['data']['file']['id']);
                if ($file === null) {
                    throw new NotFoundHttpException('File with an id ' . $fragment['data']['file']['id'] . ' was not found');
                }
                if ($file->getCopyright() !== '') {
                    $copyrights[$file->getId()->toRfc4122()] = $file->getCopyright();
                }
            }


            if ($fragment['type'] === 'imageRow') {
                foreach ($fragment['data']['images'] as $imageFragment) {
                    $file = $this->fileRepo->find($imageFragment['id']);
                    if ($file === null) {
                        throw new NotFoundHttpException('File with an id ' . $imageFragment['id'] . ' was not found');
                    }

                    if ($file->getCopyright() !== '') {
                        $copyrights[$file->getId()->toRfc4122()] = $file->getCopyright();
                    }
                }
            }
        }

        return $copyrights;
    }

    /**
     * @param EditorBlock $fragments
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
            if ($fragments['blocks'][$i]['type'] === 'header') {
                $header = $fragments['blocks'][$i]['data']['text'];
            } else {
                $this->throwUnexpectedValueException('header', $fragments['blocks'][$i]['type']);
            }

            if ($fragments['blocks'][$i + 1]['type'] === 'paragraph') {
                $par = $fragments['blocks'][$i + 1]['data']['text'];
            } else {
                $this->throwUnexpectedValueException('paragraph', $fragments['blocks'][$i]['type']);
            }

            $elements[] = [
                'header' => $header,
                'paragraph' => $par
            ];
        }

        return $elements;
    }

    private function throwUnexpectedValueException(string $expected, string $given): never
    {
        $message = sprintf('Expected timeline element of type %s, but %s given.', $expected, $given);

        throw new UnexpectedValueException($message);
    }
}
