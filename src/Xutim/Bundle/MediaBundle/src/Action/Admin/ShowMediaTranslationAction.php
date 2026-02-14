<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;

final class ShowMediaTranslationAction
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly StorageAdapterInterface $storage,
    ) {
    }

    public function __invoke(string $id): BinaryFileResponse
    {
        $media = $this->mediaRepository->findById(Uuid::fromString($id));
        if ($media === null) {
            throw new NotFoundHttpException('Media not found');
        }

        $absolutePath = $this->storage->absolutePath($media->originalPath());

        $response = new BinaryFileResponse($absolutePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);

        return $response;
    }
}
