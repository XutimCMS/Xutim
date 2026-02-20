<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Uid\Uuid;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;

final class JsonListFilesAction
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly MediaFolderRepositoryInterface $folderRepository,
        private readonly StorageAdapterInterface $storage,
    ) {
    }

    public function __invoke(
        Request $request,
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
        $qb->andWhere('m.mime NOT LIKE :mimePrefix')
            ->setParameter('mimePrefix', 'image/%');

        /** @var QueryAdapter<MediaInterface> $adapter */
        $adapter = new QueryAdapter($qb);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage($adapter, $page, $pageLength);

        /** @var list<MediaInterface> $items */
        $items = iterator_to_array($pager->getCurrentPageResults(), false);

        return new JsonResponse([
            'folders' => array_map(
                fn (MediaFolderInterface $f) => [
                    'id' => $f->id()->toRfc4122(),
                    'name' => $f->name(),
                ],
                $folders
            ),
            'items' => array_map(
                function (MediaInterface $media) use ($request): array {
                    $trans = $media->getTranslationByLocale($request->getLocale());
                    $name = $trans !== null ? $trans->name() : $media->originalPath();

                    return [
                        'id' => $media->id()->toRfc4122(),
                        'name' => $name,
                        'url' => $this->storage->url($media->originalPath()),
                        'extension' => $media->originalExt(),
                        'size' => $media->sizeBytes(),
                    ];
                },
                $items
            ),
            'totalPages' => $pager->getNbPages(),
            'folderPath' => $folderPath,
        ]);
    }
}
