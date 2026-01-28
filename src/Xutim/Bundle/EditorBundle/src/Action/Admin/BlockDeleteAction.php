<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Action\Admin;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\UX\Turbo\TurboBundle;
use Twig\Environment;
use Xutim\EditorBundle\Repository\ContentBlockRepository;
use Xutim\SecurityBundle\Security\UserRoles;

final class BlockDeleteAction
{
    public function __construct(
        private readonly ContentBlockRepository $blockRepository,
        private readonly Environment $twig,
        private readonly Security $security,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$this->security->isGranted(UserRoles::ROLE_EDITOR)) {
            throw new AccessDeniedHttpException();
        }

        $block = $this->blockRepository->find($id);
        if ($block === null) {
            throw new NotFoundHttpException('Block not found');
        }

        $blockId = $block->getId()->toRfc4122();
        $draft = $block->getDraft();
        $parent = $block->getParent();
        $slot = $block->getSlot();
        $deletedPosition = $block->getPosition();

        $this->blockRepository->remove($block, true);

        $siblings = $this->blockRepository->findByDraftAndParent($draft, $parent, $slot);
        foreach ($siblings as $sibling) {
            if ($sibling->getPosition() > $deletedPosition) {
                $sibling->setPosition($sibling->getPosition() - 1);
            }
        }
        $this->blockRepository->flush();

        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return new Response($this->twig->render('@XutimEditor/admin/block/_delete_stream.html.twig', [
                'blockId' => $blockId,
            ]));
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
