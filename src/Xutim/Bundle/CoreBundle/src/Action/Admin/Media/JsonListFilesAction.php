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
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Service\FileUploader;
use Xutim\CoreBundle\Service\ListFilterBuilder;

class JsonListFilesAction extends AbstractController
{
    public function __construct(
        private readonly FileRepository $fileRepository,
        private readonly CacheManager $cacheManager,
        private readonly ListFilterBuilder $filterBuilder,
        private readonly string $publicUploadsDirectory,
        private readonly FileUploader $fileUploader
    ) {
    }

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
        $adapter = new QueryAdapter($this->fileRepository->queryNonImagesByFilter($filter));
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        /** @var array<File> $files */
        $files = iterator_to_array($pager->getCurrentPageResults(), false);

        $fileUrls = [
            'items' => array_map(
                fn (File $file) => [
                    'id' => $file->getId(),
                    'name' => $file->getTranslationByLocaleOrAny($request->getLocale())->getName(),
                    'url' => sprintf('%s%s', $this->publicUploadsDirectory, $file->getFileName()),
                    'extension' => $file->getExtension(),
                    'size' => $this->fileUploader->getFileSize($file)
                ],
                $files
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
