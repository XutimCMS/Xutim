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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Service\ListFilterBuilder;

class JsonListImagesAction extends AbstractController
{
    public function __construct(
        private readonly FileRepository $fileRepository,
        private readonly CacheManager $cacheManager,
        private readonly ListFilterBuilder $filterBuilder,
        private readonly string $publicUploadsDirectory
    ) {
    }

    #[Route('/json/image/list', name: 'admin_json_image_list', methods: ['get'])]
    public function __invoke(
        Request $request,
        #[MapQueryParameter]
        string $searchTerm = '',
        #[MapQueryParameter]
        int $page = 1,
        #[MapQueryParameter]
        int $pageLength = 10
    ): Response {
        $filter = $this->filterBuilder->buildFilter($searchTerm, $page, $pageLength, 'createdAt', 'desc');

        /** @var QueryAdapter<File> $adapter */
        $adapter = new QueryAdapter($this->fileRepository->queryImagesByFilter($filter));
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );
        /** @var array<File> $images */
        $images = iterator_to_array($pager->getCurrentPageResults(), false);

        $fileUrls = [
            'items' => array_map(
                fn (File $file) => [
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
            'totalPages' => $pager->getNbPages()
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
