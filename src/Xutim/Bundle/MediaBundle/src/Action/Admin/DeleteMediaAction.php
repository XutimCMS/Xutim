<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Uid\Uuid;
use Twig\Environment;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Form\Admin\DeleteType;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\MediaBundle\Domain\Event\MediaDeletedEvent;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaTranslationRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;
use Xutim\MediaBundle\Service\VariantCleaner;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

final class DeleteMediaAction
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly MediaTranslationRepositoryInterface $translationRepository,
        private readonly MediaVariantRepositoryInterface $variantRepository,
        private readonly VariantCleaner $variantCleaner,
        private readonly StorageAdapterInterface $storage,
        private readonly LogEventFactory $logEventFactory,
        private readonly LogEventRepository $logEventRepository,
        private readonly UserStorage $userStorage,
        private readonly FormFactoryInterface $formFactory,
        private readonly Environment $twig,
        private readonly AdminUrlGenerator $router,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly string $mediaClass,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$this->authorizationChecker->isGranted(UserRoles::ROLE_EDITOR)) {
            throw new AccessDeniedHttpException();
        }

        $media = $this->mediaRepository->findById(Uuid::fromString($id));
        if ($media === null) {
            throw new NotFoundHttpException('Media not found');
        }

        $form = $this->formFactory->create(DeleteType::class, [], [
            'action' => $this->router->generate('admin_media_delete', ['id' => $id]),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $folderId = $media->folder()?->id()->toRfc4122();
            $mediaId = $media->id();
            $originalPath = $media->originalPath();

            $this->variantCleaner->cleanForMedia($media);
            $this->variantRepository->deleteByMedia($media);
            $this->translationRepository->removeByMedia($media);
            $this->storage->delete($originalPath);
            $this->mediaRepository->remove($media, true);

            $event = new MediaDeletedEvent($mediaId, $originalPath);
            $logEntry = $this->logEventFactory->create(
                $mediaId,
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                $this->mediaClass,
                $event,
            );
            $this->logEventRepository->save($logEntry, true);

            return new RedirectResponse(
                $this->router->generate('admin_media_list', ['id' => $folderId]),
            );
        }

        $content = $this->twig->render('@XutimMedia/admin/delete.html.twig', [
            'media' => $media,
            'form' => $form->createView(),
        ]);

        return new Response($content);
    }
}
