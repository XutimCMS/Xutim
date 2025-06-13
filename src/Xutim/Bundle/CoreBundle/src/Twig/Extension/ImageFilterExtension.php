<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension;

use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\Uid\UuidV4;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Repository\FileRepository;

class ImageFilterExtension extends AbstractExtension
{
    public function __construct(
        private readonly FilterService $filterService,
        private readonly FileRepository $fileRepo
    ) {
    }
    public function getFilters(): array
    {
        return [
            new TwigFilter('xutim_image_filter_from_ref', [$this, 'filterFromRef']),
            new TwigFilter('xutim_image_file_from_id', [$this, 'fileFromId']),
        ];
    }

    public function fileFromId(string $id): FileInterface
    {
        $file = $this->fileRepo->find(new UuidV4($id));

        if ($file === null) {
            throw new \Exception('File was not found.');
        }

        return $file;
    }

    public function filterFromRef(string $url, string $filter): string
    {
        $pattern = '#^(?:https?://[^/]+)?/file/(?P<ref>[^/]+)\.(?P<extension>[a-zA-Z0-9]+)$#';
        if (preg_match($pattern, $url, $matches) !== 1) {
            return $url;
        }

        $ref = $matches['ref'];

        $file = $this->fileRepo->findOneBy(['reference' => $ref]);
        if ($file === null) {
            return $url;
        }

        $imagePath = $this->filterService->getUrlOfFilteredImage(
            $file->getFileName(),
            $filter,
            null,
            true
        );

        return $imagePath;
    }
}
