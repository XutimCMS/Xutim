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
use Xutim\EditorBundle\Entity\Block\HeadingBlock;
use Xutim\EditorBundle\Entity\Block\ParagraphBlock;
use Xutim\EditorBundle\Entity\Block\QuoteBlock;
use Xutim\EditorBundle\Repository\ContentBlockRepository;
use Xutim\SecurityBundle\Security\UserRoles;

final class BlockConvertAction
{
    private const array CONVERTIBLE_TYPES = [
        ParagraphBlock::TYPE,
        HeadingBlock::TYPE,
        QuoteBlock::TYPE,
    ];

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

        /** @var array{type?: string} $data */
        $data = json_decode($request->getContent(), true) ?? [];
        $newType = $data['type'] ?? null;

        if ($newType === null || !in_array($newType, self::CONVERTIBLE_TYPES, true)) {
            throw new BadRequestHttpException('Invalid block type');
        }

        $currentType = $block->getType();
        if ($currentType === $newType) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        // Only allow conversion between text block types
        if (!in_array($currentType, self::CONVERTIBLE_TYPES, true)) {
            throw new BadRequestHttpException('Cannot convert this block type');
        }

        // Convert the block type using native SQL (use RFC4122 format for the SQL)
        $convertedBlock = $this->blockRepository->convertType($block->getId()->toRfc4122(), $newType);

        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return new Response($this->twig->render('@XutimEditor/admin/block/_convert_stream.html.twig', [
                'block' => $convertedBlock,
            ]));
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
