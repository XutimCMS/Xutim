<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Uid\Uuid;
use Twig\Environment;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\MediaBundle\Domain\Event\MediaTranslationUpdatedEvent;
use Xutim\MediaBundle\Domain\Model\MediaTranslationInterface;
use Xutim\MediaBundle\Form\Admin\MediaTranslationType;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaTranslationRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;
use Xutim\MediaBundle\Service\PresetRegistry;
use Xutim\MediaBundle\Service\VariantPathResolver;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SecurityBundle\Service\UserStorage;

final class EditMediaAction
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly MediaTranslationRepositoryInterface $translationRepository,
        private readonly MediaVariantRepositoryInterface $variantRepository,
        private readonly PresetRegistry $presetRegistry,
        private readonly VariantPathResolver $pathResolver,
        private readonly ContentContext $contentContext,
        private readonly TranslatorAuthChecker $transAuthChecker,
        private readonly LogEventFactory $logEventFactory,
        private readonly LogEventRepository $logEventRepository,
        private readonly UserStorage $userStorage,
        private readonly string $mediaTranslationClass,
        private readonly string $mediaClass,
        private readonly FormFactoryInterface $formFactory,
        private readonly Environment $twig,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $media = $this->mediaRepository->findById(Uuid::fromString($id));
        if ($media === null) {
            throw new NotFoundHttpException('Media not found');
        }

        if (!$this->authorizationChecker->isGranted(UserRoles::ROLE_EDITOR)) {
            throw new AccessDeniedHttpException();
        }

        $locale = $this->contentContext->getLanguage();
        $translation = $media->getTranslationByLocale($locale);

        $form = $this->formFactory->create(MediaTranslationType::class, [
            'name' => $translation?->name() ?? '',
            'alt' => $translation?->alt() ?? '',
        ], [
            'disabled' => $this->transAuthChecker->canTranslate($locale) === false,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->transAuthChecker->denyUnlessCanTranslate($locale);
            /** @var array{name: string, alt: string|null} $data */
            $data = $form->getData();

            if ($translation === null) {
                /** @var MediaTranslationInterface $translation */
                $translation = new ($this->mediaTranslationClass)(
                    $media,
                    $locale,
                    $data['name'],
                    $data['alt'] ?? '',
                );
                $media->addTranslation($translation);
            } else {
                $translation->change($data['name'], $data['alt'] ?? '');
            }

            $this->translationRepository->save($translation, true);

            $event = new MediaTranslationUpdatedEvent(
                $media->id(),
                $locale,
                $data['name'],
                $data['alt'] ?? '',
            );
            $logEntry = $this->logEventFactory->create(
                $media->id(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                $this->mediaClass,
                $event,
            );
            $this->logEventRepository->save($logEntry, true);

            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getBag('flashes');
            $flashBag->add('success', 'Changes were made successfully.');

            return new RedirectResponse($this->urlGenerator->generate('admin_media_edit', [
                'id' => $id,
                '_content_locale' => $locale,
            ]));
        }

        $presets = $this->presetRegistry->all();
        $previewData = [];

        foreach ($presets as $preset) {
            $variants = $this->variantRepository->findByMediaPreset($media, $preset->name);

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

        $content = $this->twig->render('@XutimMedia/admin/edit.html.twig', [
            'media' => $media,
            'translation' => $translation,
            'form' => $form->createView(),
            'previewData' => $previewData,
        ]);

        return new Response($content);
    }
}
