<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

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
use Xutim\MediaBundle\Domain\Event\MediaMovedEvent;
use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

final class MoveMediaAction
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly MediaFolderRepositoryInterface $folderRepository,
        private readonly LogEventFactory $logEventFactory,
        private readonly LogEventRepository $logEventRepository,
        private readonly UserStorage $userStorage,
        private readonly Environment $twig,
        private readonly AdminUrlGenerator $router,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly string $mediaClass,
    ) {
    }

    public function __invoke(Request $request, string $id, ?string $folderId = null): Response
    {
        if (!$this->authorizationChecker->isGranted(UserRoles::ROLE_EDITOR)) {
            throw new AccessDeniedHttpException();
        }

        $media = $this->mediaRepository->findById(Uuid::fromString($id));
        if ($media === null) {
            throw new NotFoundHttpException('Media not found');
        }

        if ($request->isMethod('POST')) {
            $folder = null;

            if ($folderId !== null && $folderId !== '') {
                $folder = $this->folderRepository->findById(Uuid::fromString($folderId));
                if ($folder === null) {
                    throw new NotFoundHttpException('Media folder not found');
                }
            }

            $currentFolder = $media->folder();
            $fromFolderId = $currentFolder?->id();
            $media->changeFolder($folder);
            $this->mediaRepository->save($media, true);

            $event = new MediaMovedEvent($media->id(), $fromFolderId, $folder?->id());
            $logEntry = $this->logEventFactory->create(
                $media->id(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                $this->mediaClass,
                $event,
            );
            $this->logEventRepository->save($logEntry, true);

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                /** @var FlashBagInterface $flashBag */
                $flashBag = $request->getSession()->getBag('flashes');

                $removeStream = $this->twig->load('@XutimMedia/admin/move.html.twig')
                    ->renderBlock('stream_remove_file', ['media' => $media]);
                $flashBag->add('stream', $removeStream);

                if ($currentFolder !== null) {
                    $refreshedCurrentFolder = $this->folderRepository->findById($currentFolder->id());
                    if ($refreshedCurrentFolder !== null) {
                        $updateSourceStream = $this->twig->load('@XutimMedia/admin/move.html.twig')
                            ->renderBlock('stream_update_folder', ['folder' => $refreshedCurrentFolder]);
                        $flashBag->add('stream', $updateSourceStream);
                    }
                }

                if ($folder !== null) {
                    $refreshedDestFolder = $this->folderRepository->findById($folder->id());
                    if ($refreshedDestFolder !== null) {
                        $updateDestStream = $this->twig->load('@XutimMedia/admin/move.html.twig')
                            ->renderBlock('stream_update_folder', ['folder' => $refreshedDestFolder]);
                        $flashBag->add('stream', $updateDestStream);
                    }
                }
            }

            $redirectId = $currentFolder?->id()->toRfc4122();

            return new RedirectResponse(
                $this->router->generate('admin_media_list', ['id' => $redirectId]),
                Response::HTTP_SEE_OTHER,
            );
        }

        $folders = $this->folderRepository->findAll();

        $content = $this->twig->render('@XutimMedia/admin/move.html.twig', [
            'media' => $media,
            'folders' => $folders,
            'currentFolder' => $media->folder(),
        ]);

        return new Response($content);
    }
}
