<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\MediaBundle\Domain\Event\MediaMovedEvent;
use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\SecurityBundle\Service\UserStorage;

final class MoveMediaToFolderAction
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly MediaFolderRepositoryInterface $folderRepository,
        private readonly LogEventFactory $logEventFactory,
        private readonly LogEventRepository $logEventRepository,
        private readonly UserStorage $userStorage,
        private readonly string $mediaClass,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var array{fileId: string, targetFolderId: string} $data */
        $data = json_decode($request->getContent(), true);
        $fileId = $data['fileId'];
        $folderId = $data['targetFolderId'];

        $media = $this->mediaRepository->findById(Uuid::fromString($fileId));
        if ($media === null) {
            return new JsonResponse(['error' => 'Media not found'], Response::HTTP_NOT_FOUND);
        }

        $folder = $this->folderRepository->findById(Uuid::fromString($folderId));
        if ($folder === null) {
            return new JsonResponse(['error' => 'Media folder not found'], Response::HTTP_NOT_FOUND);
        }

        $fromFolderId = $media->folder()?->id();
        $media->changeFolder($folder);
        $this->mediaRepository->save($media, true);

        $event = new MediaMovedEvent($media->id(), $fromFolderId, $folder->id());
        $logEntry = $this->logEventFactory->create(
            $media->id(),
            $this->userStorage->getUserWithException()->getUserIdentifier(),
            $this->mediaClass,
            $event,
        );
        $this->logEventRepository->save($logEntry, true);

        return new JsonResponse(['success' => true]);
    }
}
