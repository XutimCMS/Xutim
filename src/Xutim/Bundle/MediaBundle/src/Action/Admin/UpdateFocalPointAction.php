<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\MediaBundle\Domain\Event\MediaFocalPointUpdatedEvent;
use Xutim\MediaBundle\Message\RegenerateVariantsMessage;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

final class UpdateFocalPointAction
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly EntityManagerInterface $em,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly MessageBusInterface $messageBus,
        private readonly LogEventFactory $logEventFactory,
        private readonly LogEventRepository $logEventRepository,
        private readonly UserStorage $userStorage,
        private readonly string $mediaClass,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $media = $this->mediaRepository->findById(Uuid::fromString($id));
        if ($media === null) {
            return new JsonResponse(['error' => 'Media not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->authorizationChecker->isGranted(UserRoles::ROLE_EDITOR)) {
            throw new AccessDeniedException();
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $focalX = isset($data['focalX']) && is_numeric($data['focalX']) ? (float) $data['focalX'] : null;
        $focalY = isset($data['focalY']) && is_numeric($data['focalY']) ? (float) $data['focalY'] : null;

        if ($focalX === null || $focalY === null) {
            return new JsonResponse(['error' => 'Missing focalX or focalY'], Response::HTTP_BAD_REQUEST);
        }

        if ($focalX < 0 || $focalX > 1 || $focalY < 0 || $focalY > 1) {
            return new JsonResponse(['error' => 'Focal point must be between 0 and 1'], Response::HTTP_BAD_REQUEST);
        }

        $media->change(
            copyright: null,
            focalX: $focalX,
            focalY: $focalY,
            folder: null,
            blurHash: null,
        );

        $this->em->flush();

        $event = new MediaFocalPointUpdatedEvent($media->id(), $focalX, $focalY);
        $logEntry = $this->logEventFactory->create(
            $media->id(),
            $this->userStorage->getUserWithException()->getUserIdentifier(),
            $this->mediaClass,
            $event,
        );
        $this->logEventRepository->save($logEntry, true);

        $this->messageBus->dispatch(new RegenerateVariantsMessage($media->id()));

        return new JsonResponse([
            'success' => true,
            'focalX' => $media->focalX(),
            'focalY' => $media->focalY(),
        ]);
    }
}
