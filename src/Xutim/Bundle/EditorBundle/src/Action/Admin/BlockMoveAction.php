<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Action\Admin;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\EditorBundle\Repository\ContentBlockRepository;
use Xutim\SecurityBundle\Security\UserRoles;

final class BlockMoveAction
{
    public function __construct(
        private readonly ContentBlockRepository $blockRepository,
        private readonly Security $security,
    ) {
    }

    public function __invoke(string $id, int $position): Response
    {
        if (!$this->security->isGranted(UserRoles::ROLE_EDITOR)) {
            throw new AccessDeniedHttpException();
        }

        $block = $this->blockRepository->find($id);
        if ($block === null) {
            throw new NotFoundHttpException('Block not found');
        }

        $oldPosition = $block->getPosition();

        if ($position === $oldPosition) {
            return new JsonResponse(['success' => true]);
        }

        $draft = $block->getDraft();
        $parent = $block->getParent();
        $slot = $block->getSlot();

        $siblings = $this->blockRepository->findByDraftAndParent($draft, $parent, $slot);

        foreach ($siblings as $sibling) {
            if ($sibling->getId() === $block->getId()) {
                continue;
            }

            $siblingPosition = $sibling->getPosition();

            if ($position > $oldPosition) {
                if ($siblingPosition > $oldPosition && $siblingPosition <= $position) {
                    $sibling->setPosition($siblingPosition - 1);
                }
            } else {
                if ($siblingPosition >= $position && $siblingPosition < $oldPosition) {
                    $sibling->setPosition($siblingPosition + 1);
                }
            }
        }

        $block->setPosition($position);
        $this->blockRepository->flush();

        return new JsonResponse(['success' => true]);
    }
}
