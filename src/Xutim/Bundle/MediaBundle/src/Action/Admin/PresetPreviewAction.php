<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Uid\Uuid;
use Twig\Environment;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;
use Xutim\MediaBundle\Service\PresetRegistry;
use Xutim\MediaBundle\Service\VariantPathResolver;
use Xutim\SecurityBundle\Security\UserRoles;

final class PresetPreviewAction
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly MediaVariantRepositoryInterface $variantRepository,
        private readonly PresetRegistry $presetRegistry,
        private readonly VariantPathResolver $pathResolver,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $media = $this->mediaRepository->findById(Uuid::fromString($id));
        if ($media === null) {
            throw new NotFoundHttpException('Media not found');
        }

        if (!$this->authorizationChecker->isGranted(UserRoles::ROLE_EDITOR)) {
            throw new AccessDeniedException();
        }

        if (!$media->isImage()) {
            throw new NotFoundHttpException('Media is not an image');
        }

        $presets = $this->presetRegistry->all();
        $previewData = [];

        foreach ($presets as $preset) {
            $variants = $this->variantRepository->findByMediaPresetFormat(
                $media,
                $preset->name,
                'webp',
            );

            $presetData = [
                'preset' => $preset,
                'variants' => [],
            ];

            foreach ($variants as $variant) {
                $url = $this->pathResolver->getUrl(
                    $media,
                    $preset,
                    $variant->width(),
                    $variant->format(),
                    $variant->fingerprint(),
                );

                $presetData['variants'][] = [
                    'variant' => $variant,
                    'url' => $url,
                ];
            }

            $previewData[] = $presetData;
        }

        $content = $this->twig->render('@XutimMedia/admin/preset_preview.html.twig', [
            'media' => $media,
            'previewData' => $previewData,
        ]);

        return new Response($content);
    }
}
