<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Uid\Uuid;
use Xutim\MediaBundle\Message\RegenerateVariantsMessage;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\SecurityBundle\Security\UserRoles;

final class RegenerateVariantsAction
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $media = $this->mediaRepository->findById(Uuid::fromString($id));
        if ($media === null) {
            return new JsonResponse(['error' => 'Media not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->authorizationChecker->isGranted(UserRoles::ROLE_ADMIN)) {
            throw new AccessDeniedException();
        }

        if (!$media->isImage()) {
            return new JsonResponse(['error' => 'Media is not an image'], Response::HTTP_BAD_REQUEST);
        }

        $this->messageBus->dispatch(new RegenerateVariantsMessage($media->id()));

        return new JsonResponse(['status' => 'processing']);
    }
}
