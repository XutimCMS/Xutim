<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Uid\Uuid;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;
use Xutim\MediaBundle\Service\VariantPathResolver;

final class JsonListImagesAction
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly MediaFolderRepositoryInterface $folderRepository,
        private readonly MediaVariantRepositoryInterface $variantRepository,
        private readonly VariantPathResolver $pathResolver,
        private readonly StorageAdapterInterface $storage,
    ) {
    }

    public function __invoke(
        #[MapQueryParameter]
        string $searchTerm = '',
        #[MapQueryParameter]
        int $page = 1,
        #[MapQueryParameter]
        int $pageLength = 10,
        #[MapQueryParameter]
        ?string $folderId = null,
    ): JsonResponse {
        $folder = null;
        if ($folderId !== null) {
            $folder = $this->folderRepository->findById(Uuid::fromString($folderId));
        }

        $folders = $this->folderRepository->findByParent($folder);
        $folderPath = [];
        if ($folder !== null) {
            $folderPath = array_map(
                fn (MediaFolderInterface $f) => ['id' => $f->id()->toRfc4122(), 'name' => $f->name()],
                $folder->folderPath()
            );
        }

        $qb = $this->mediaRepository->queryByFolderAndSearch($folder, $searchTerm);
        $qb->andWhere('m.mime LIKE :mimePrefix')
            ->setParameter('mimePrefix', 'image/%');

        /** @var QueryAdapter<MediaInterface> $adapter */
        $adapter = new QueryAdapter($qb);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage($adapter, $page, $pageLength);

        /** @var list<MediaInterface> $images */
        $images = iterator_to_array($pager->getCurrentPageResults(), false);

        return new JsonResponse([
            'folders' => array_map(
                fn (MediaFolderInterface $f) => [
                    'id' => $f->id()->toRfc4122(),
                    'name' => $f->name(),
                ],
                $folders
            ),
            'items' => array_map(
                fn (MediaInterface $media) => [
                    'filteredUrl' => $this->resolveThumbUrl($media),
                    'fullSourceUrl' => $this->storage->url($media->originalPath()),
                    'id' => $media->id()->toRfc4122(),
                ],
                $images
            ),
            'totalPages' => $pager->getNbPages(),
            'folderPath' => $folderPath,
        ]);
    }

    private function resolveThumbUrl(MediaInterface $media): string
    {
        $variant = $this->variantRepository->findByMediaPresetFormatWidth($media, 'thumb_small', 'webp', 227);

        if ($variant !== null) {
            return $this->pathResolver->getUrl($variant);
        }

        return $this->storage->url($media->originalPath());
    }
}
