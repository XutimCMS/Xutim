<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Action\Admin;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Xutim\EditorBundle\Repository\ContentBlockRepository;
use Xutim\SecurityBundle\Security\UserRoles;

final class BlockViewAction
{
    public function __construct(
        private readonly ContentBlockRepository $blockRepository,
        private readonly Environment $twig,
        private readonly Security $security,
    ) {
    }

    public function __invoke(string $id): Response
    {
        if (!$this->security->isGranted(UserRoles::ROLE_EDITOR)) {
            throw new AccessDeniedHttpException();
        }

        $block = $this->blockRepository->find($id);
        if ($block === null) {
            throw new NotFoundHttpException('Block not found');
        }

        return new Response($this->twig->render('@XutimEditor/admin/block/_preview.html.twig', [
            'block' => $block,
        ]));
    }
}
