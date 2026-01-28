<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Action\Admin;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\EditorBundle\Repository\ContentBlockRepository;
use Xutim\SecurityBundle\Security\UserRoles;

final class BlockPatchAction
{
    public function __construct(
        private readonly ContentBlockRepository $blockRepository,
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

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new BadRequestHttpException('Invalid JSON');
        }

        // Update allowed fields
        if (isset($data['html']) && is_string($data['html']) && method_exists($block, 'setHtml')) {
            $block->setHtml($this->sanitizeHtml($data['html']));
        }

        if (isset($data['level']) && is_numeric($data['level']) && method_exists($block, 'setLevel')) {
            $block->setLevel((int) $data['level']);
        }

        if (isset($data['caption']) && is_string($data['caption']) && method_exists($block, 'setCaption')) {
            $block->setCaption($data['caption']);
        }

        $this->blockRepository->flush();

        return new JsonResponse([
            'success' => true,
            'id' => $block->getId()->toRfc4122(),
        ]);
    }

    private function sanitizeHtml(string $html): string
    {
        // Allow only safe inline tags
        $allowed = '<strong><em><b><i><a><br><span>';
        $html = strip_tags($html, $allowed);

        // Basic XSS prevention - remove event handlers
        $html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html) ?? $html;

        return $html;
    }
}
