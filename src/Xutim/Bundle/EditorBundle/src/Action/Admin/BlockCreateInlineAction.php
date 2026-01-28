<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Action\Admin;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\UX\Turbo\TurboBundle;
use Twig\Environment;
use Xutim\EditorBundle\Domain\Factory\ContentBlockFactory;
use Xutim\EditorBundle\Repository\ContentBlockRepository;
use Xutim\EditorBundle\Repository\ContentDraftRepository;
use Xutim\SecurityBundle\Security\UserRoles;

final class BlockCreateInlineAction
{
    public function __construct(
        private readonly ContentDraftRepository $draftRepository,
        private readonly ContentBlockRepository $blockRepository,
        private readonly ContentBlockFactory $blockFactory,
        private readonly Environment $twig,
        private readonly Security $security,
    ) {
    }

    public function __invoke(Request $request, string $draftId): Response
    {
        if (!$this->security->isGranted(UserRoles::ROLE_EDITOR)) {
            throw new AccessDeniedHttpException();
        }

        $draft = $this->draftRepository->find($draftId);
        if ($draft === null) {
            throw new NotFoundHttpException('Draft not found');
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new BadRequestHttpException('Invalid JSON');
        }

        $blockType = is_string($data['type'] ?? null) ? $data['type'] : 'paragraph';
        $html = is_string($data['html'] ?? null) ? $data['html'] : '';
        $afterBlockId = is_string($data['afterBlockId'] ?? null) ? $data['afterBlockId'] : null;
        $parentBlockId = is_string($data['parentBlockId'] ?? null) ? $data['parentBlockId'] : null;
        $requestedSlot = isset($data['slot']) && (is_int($data['slot']) || is_string($data['slot']))
            ? (int) $data['slot']
            : null;

        // Find the block to insert after
        $afterBlock = null;
        $position = 0;
        $parent = null;
        $slot = null;

        if ($afterBlockId !== null && $afterBlockId !== '') {
            $afterBlock = $this->blockRepository->find($afterBlockId);
            if ($afterBlock !== null) {
                $position = $afterBlock->getPosition() + 1;
                $parent = $afterBlock->getParent();
                $slot = $afterBlock->getSlot();

                // Shift positions of subsequent blocks
                $siblings = $this->blockRepository->findByDraftAndParent($draft, $parent, $slot);
                foreach ($siblings as $sibling) {
                    if ($sibling->getPosition() >= $position) {
                        $sibling->setPosition($sibling->getPosition() + 1);
                    }
                }
            }
        } elseif ($parentBlockId !== null && $parentBlockId !== '') {
            // Creating block inside a layout slot
            $parent = $this->blockRepository->find($parentBlockId);
            if ($parent !== null) {
                $slot = $requestedSlot;
                $existingBlocks = $this->blockRepository->findByDraftAndParent($draft, $parent, $slot);
                $position = count($existingBlocks);
            }
        } else {
            // No afterBlockId or parent - add at end of top-level
            $existingBlocks = $this->blockRepository->findByDraftAndParent($draft, null, null);
            $position = count($existingBlocks);
        }

        // Create the new block
        $block = $this->blockFactory->create($blockType, $draft, $parent, $slot, $position);

        // Set initial HTML content if provided
        if ($html !== '' && method_exists($block, 'setHtml')) {
            $block->setHtml($this->sanitizeHtml($html));
        }

        $this->blockRepository->save($block, true);

        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

        return new Response($this->twig->render('@XutimEditor/admin/block/_create_inline_stream.html.twig', [
            'block' => $block,
            'afterBlockId' => $afterBlockId,
            'parentBlockId' => $parentBlockId,
            'slot' => $slot,
            'draft' => $draft,
        ]));
    }

    private function sanitizeHtml(string $html): string
    {
        $allowed = '<strong><em><b><i><a><br><span>';
        $html = strip_tags($html, $allowed);
        $html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html) ?? $html;

        return $html;
    }
}
