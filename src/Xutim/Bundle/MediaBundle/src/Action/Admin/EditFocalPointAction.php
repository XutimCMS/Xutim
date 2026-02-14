<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Uid\Uuid;
use Twig\Environment;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\SecurityBundle\Security\UserRoles;

final class EditFocalPointAction
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly Environment $twig,
        private readonly AdminUrlGenerator $router,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function __invoke(string $id): Response
    {
        if (!$this->authorizationChecker->isGranted(UserRoles::ROLE_EDITOR)) {
            throw new AccessDeniedHttpException();
        }

        $media = $this->mediaRepository->findById(Uuid::fromString($id));
        if ($media === null) {
            throw new NotFoundHttpException('Media not found');
        }

        if (!$media->isImage()) {
            throw new NotFoundHttpException('Media is not an image');
        }

        $content = $this->twig->render('@XutimMedia/admin/edit_focal_point.html.twig', [
            'media' => $media,
            'saveUrl' => $this->router->generate('admin_media_focal_point', ['id' => $media->id()]),
            'editUrl' => $this->router->generate('admin_media_edit', ['id' => $media->id()]),
        ]);

        return new Response($content);
    }
}
