<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension;

use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\Uid\UuidV4;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;

class ImageFilterExtension extends AbstractExtension
{
    public function __construct(
        private readonly FilterService $filterService,
        private readonly MediaRepositoryInterface $mediaRepo
    ) {
    }
    public function getFilters(): array
    {
        return [
            new TwigFilter('xutim_image_filter_from_ref', [$this, 'filterFromRef']),
            new TwigFilter('xutim_image_file_from_id', [$this, 'fileFromId']),
        ];
    }

    public function fileFromId(string $id): MediaInterface
    {
        $media = $this->mediaRepo->findById(new UuidV4($id));

        if ($media === null) {
            throw new \Exception('Media was not found.');
        }

        return $media;
    }

    public function filterFromRef(string $url, string $filter): string
    {
        $pattern = '#^(?:https?://[^/]+)?/file/(?P<ref>[^/]+)\.(?P<extension>[a-zA-Z0-9]+)$#';
        if (preg_match($pattern, $url, $matches) !== 1) {
            return $url;
        }

        $ref = $matches['ref'];

        $media = $this->mediaRepo->findByOriginalPath($ref);
        if ($media === null) {
            return $url;
        }

        $imagePath = $this->filterService->getUrlOfFilteredImage(
            $media->originalPath(),
            $filter,
            null,
            true
        );

        return $imagePath;
    }
}
