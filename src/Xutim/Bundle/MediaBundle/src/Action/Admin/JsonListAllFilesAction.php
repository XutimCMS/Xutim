<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

use Symfony\Component\HttpFoundation\JsonResponse;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;

final class JsonListAllFilesAction
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $files = $this->mediaRepository->findAllNonImages();

        $titles = [];
        foreach ($files as $media) {
            $trans = $media->getTranslations()->first();
            if ($trans === false) {
                continue;
            }
            $titles[$media->id()->toRfc4122()] = $trans->name();
        }

        return new JsonResponse($titles);
    }
}
