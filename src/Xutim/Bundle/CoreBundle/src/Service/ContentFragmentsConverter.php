<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Xutim\CoreBundle\Content\CanonicalContentExtractor;
use Xutim\CoreBundle\Content\Transform\EditorJsToCanonicalDocumentTransformer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\UuidV4;
use Twig\Environment;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;

readonly class ContentFragmentsConverter
{
    public function __construct(
        private readonly Environment $twig,
        private readonly MediaRepositoryInterface $mediaRepo,
        private readonly EditorJsToCanonicalDocumentTransformer $transformer,
        private readonly CanonicalContentExtractor $extractor,
    ) {
    }

    /**
     * @param EditorBlock $fragments
     */
    public function convertToThemeHtml(array $fragments, string $themePath, string $locale): string
    {
        if (!is_array($fragments['blocks'] ?? null) || $fragments['blocks'] === []) {
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
     * @param EditorBlock           $fragments
     * @param array<string, string> $options
     */
    public function convertFragmentsToThemeHtml(
        array $fragments,
        string $themePath,
        string $locale,
        array $options = []
    ): string {
        if (!is_array($fragments['blocks'] ?? null) || $fragments['blocks'] === []) {
            return '';
        }

        return $this->twig->render(sprintf('%s/content_fragment/content.html.twig', $themePath), [
            'fragments' => $fragments,
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
        if (!is_array($fragments['blocks'] ?? null) || $fragments['blocks'] === []) {
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
        return $this->extractor->extractIntroduction($this->transformer->transform($fragments));
    }

    /**
     * @param EditorBlock $fragments
     */
    public function extractParagraphs(array $fragments, int $num): string
    {
        return $this->extractor->extractParagraphsHtml($this->transformer->transform($fragments), $num);
    }

    /**
     * @param EditorBlock $fragments
     *
     * @return array<string, string>
     */
    public function extractCopyrights(array $fragments): array
    {
        $document = $this->transformer->transform($fragments);
        if ($document->blocks === []) {
            return [];
        }

        $copyrights = [];
        foreach ($this->extractor->flattenBlocks($document->blocks) as $block) {
            if ($block->kind === 'image') {
                $media = $this->mediaRepo->findById(new UuidV4((string) ($block->attrs['fileId'] ?? '')));
                if ($media === null) {
                    throw new NotFoundHttpException('Media with an id ' . ($block->attrs['fileId'] ?? '') . ' was not found');
                }
                if ($media->copyright() !== null && $media->copyright() !== '') {
                    $copyrights[$media->id()->toRfc4122()] = $media->copyright();
                }
            }

            if ($block->kind === 'image_gallery') {
                foreach ($block->galleryImages as $galleryImage) {
                    if ($galleryImage->id === null || $galleryImage->id === '') {
                        continue;
                    }
                    $media = $this->mediaRepo->findById(new UuidV4($galleryImage->id));
                    if ($media === null) {
                        throw new NotFoundHttpException('Media with an id ' . $galleryImage->id . ' was not found');
                    }

                    if ($media->copyright() !== null && $media->copyright() !== '') {
                        $copyrights[$media->id()->toRfc4122()] = $media->copyright();
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
        return $this->extractor->extractTimelineElements($this->transformer->transform($fragments));
    }
}
