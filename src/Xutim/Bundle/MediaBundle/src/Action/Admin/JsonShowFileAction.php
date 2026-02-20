<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;

final class JsonShowFileAction
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly StorageAdapterInterface $storage,
    ) {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $media = $this->mediaRepository->findById(Uuid::fromString($id));
        if ($media === null) {
            throw new NotFoundHttpException('The media does not exist');
        }

        $trans = $media->getTranslationByLocale($request->getLocale());
        $name = $trans !== null ? $trans->name() : $media->originalPath();

        return new JsonResponse([
            'id' => $media->id()->toRfc4122(),
            'name' => $name,
            'extension' => $media->originalExt(),
            'url' => $this->storage->url($media->originalPath()),
            'size' => $media->sizeBytes(),
        ]);
    }
}
