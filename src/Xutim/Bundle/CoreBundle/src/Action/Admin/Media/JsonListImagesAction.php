<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\MediaFolderInterface;
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\MediaFolderRepository;
use Xutim\CoreBundle\Service\ListFilterBuilder;

class JsonListImagesAction extends AbstractController
{
    public function __construct(
        private readonly FileRepository $fileRepo,
        private readonly MediaFolderRepository $folderRepo,
        private readonly CacheManager $cacheManager,
        private readonly ListFilterBuilder $filterBuilder,
        private readonly string $publicUploadsDirectory
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
        ?string $folderId = null
    ): Response {
        $folder = null;
        if ($folderId !== null) {
            $folder = $this->folderRepo->find($folderId);
        }

        $folders = $this->folderRepo->findByParentFolder($folder);
        $folderPath = [];
        if ($folder !== null) {
            /** @var array<int, MediaFolderInterface> $path */
            $path = $folder->getFolderPath();
            $folderPath = array_map(
                fn (MediaFolderInterface $f) => ['id' => $f->getId(), 'name' => $f->getName()],
                $path
            );
        }

        $filter = $this->filterBuilder->buildFilter($searchTerm, $page, $pageLength, 'createdAt', 'desc');

        /** @var QueryAdapter<File> $adapter */
        $adapter = new QueryAdapter($this->fileRepo->queryByFolderAndFilter($filter, $folder));
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );
        /** @var array<File> $images */
        $images = iterator_to_array($pager->getCurrentPageResults(), false);

        $fileUrls = [
            'folders' => array_map(
                fn (MediaFolderInterface $folder) => [
                    'id' => $folder->getId(),
                    'name' => $folder->getName()
                ],
                $folders
            ),
            'items' => array_map(
                fn (FileInterface $file) => [
                    'filteredUrl' => $this->getFilteredImagePath(sprintf(
                        '%s%s',
                        $this->publicUploadsDirectory,
                        $file->getFileName(),
                    ), 'thumb_small'),
                    'fullSourceUrl' => sprintf(
                        '%s%s',
                        $this->publicUploadsDirectory,
                        $file->getFileName(),
                    ),
                    'id' => $file->getId()
                ],
                $images
            ),
            'totalPages' => $pager->getNbPages(),
            'folderPath' => $folderPath
        ];

        return $this->json($fileUrls);
    }

    public function getFilteredImagePath(string $publicPath, string $filter): string
    {
        // Strip "/public" if present (Symfony public root is assumed)
        $path = str_replace('/public', '', $publicPath);

        // Ensure the path starts with a slash
        $path = '/' . ltrim($path, '/');

        return $this->cacheManager->getBrowserPath($path, $filter, [], null, UrlGeneratorInterface::ABSOLUTE_PATH);
    }
}
