<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\UX\Turbo\TurboBundle;
use Twig\Environment;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\MediaBundle\Domain\Event\MediaUploadedEvent;
use Xutim\MediaBundle\Form\Admin\UploadMediaType;
use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;
use Xutim\MediaBundle\Service\MediaUploader;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

final class UploadMediaAction
{
    public function __construct(
        private readonly MediaUploader $uploader,
        private readonly MediaFolderRepositoryInterface $folderRepository,
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

    public function __invoke(Request $request, ?string $id = null): Response
    {
        if (!$this->authorizationChecker->isGranted(UserRoles::ROLE_EDITOR)) {
            throw new AccessDeniedHttpException();
        }

        $folder = null;
        if ($id !== null) {
            $folder = $this->folderRepository->findById(Uuid::fromString($id));
            if ($folder === null) {
                throw new NotFoundHttpException('Media folder not found');
            }
        }

        $form = $this->formFactory->create(UploadMediaType::class, null, [
            'action' => $this->router->generate('admin_media_upload', ['id' => $id]),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{file: UploadedFile, name: string, alt: string|null, copyright: string|null, locale: string} $data */
            $data = $form->getData();

            $media = $this->uploader->upload(
                $data['file'],
                $data['name'],
                $data['alt'] ?? '',
                $data['locale'],
                $data['copyright'],
                $folder,
            );

            $event = new MediaUploadedEvent(
                $media->id(),
                $media->originalPath(),
                $media->mime(),
                $media->sizeBytes(),
            );
            $logEntry = $this->logEventFactory->create(
                $media->id(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                $this->mediaClass,
                $event,
            );
            $this->logEventRepository->save($logEntry, true);

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                $stream = $this->twig->load('@XutimMedia/admin/upload.html.twig')
                    ->renderBlock('stream_success', ['media' => $media]);

                /** @var FlashBagInterface $flashBag */
                $flashBag = $request->getSession()->getBag('flashes');
                $flashBag->add('stream', $stream);
            }

            return new RedirectResponse(
                $this->router->generate('admin_media_list', ['id' => $folder?->id()->toRfc4122()]),
                Response::HTTP_SEE_OTHER,
            );
        }

        $content = $this->twig->render('@XutimMedia/admin/upload.html.twig', [
            'form' => $form->createView(),
        ]);

        return new Response($content);
    }
}
