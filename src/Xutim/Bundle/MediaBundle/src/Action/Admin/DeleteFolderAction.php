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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\UX\Turbo\TurboBundle;
use Twig\Environment;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Form\Admin\DeleteType;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\MediaBundle\Domain\Event\MediaFolderDeletedEvent;
use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

final class DeleteFolderAction
{
    public function __construct(
        private readonly MediaFolderRepositoryInterface $folderRepository,
        private readonly LogEventFactory $logEventFactory,
        private readonly LogEventRepository $logEventRepository,
        private readonly UserStorage $userStorage,
        private readonly FormFactoryInterface $formFactory,
        private readonly Environment $twig,
        private readonly AdminUrlGenerator $router,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly string $mediaFolderClass,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$this->authorizationChecker->isGranted(UserRoles::ROLE_EDITOR)) {
            throw new AccessDeniedHttpException();
        }

        $folder = $this->folderRepository->findById(Uuid::fromString($id));
        if ($folder === null) {
            throw new NotFoundHttpException('Media folder not found');
        }

        if (!$folder->children()->isEmpty() || !$folder->media()->isEmpty()) {
            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getBag('flashes');
            $flashBag->add('danger', 'Folder is not empty and cannot be deleted.');

            return new RedirectResponse(
                $this->router->generate('admin_media_list', ['id' => $id]),
            );
        }

        $form = $this->formFactory->create(DeleteType::class, [], [
            'action' => $this->router->generate('admin_media_folder_delete', ['id' => $id]),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $folderId = $folder->id();
            $folderName = $folder->name();
            $parentId = $folder->parent()?->id()->toRfc4122();

            $this->folderRepository->remove($folder, true);

            $event = new MediaFolderDeletedEvent($folderId, $folderName);
            $logEntry = $this->logEventFactory->create(
                $folderId,
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                $this->mediaFolderClass,
                $event,
            );
            $this->logEventRepository->save($logEntry, true);

            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getBag('flashes');
            $flashBag->add('success', 'Folder was deleted successfully.');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                $stream = $this->twig->load('@XutimMedia/admin/folder_delete.html.twig')
                    ->renderBlock('stream_success');

                $flashBag->add('stream', $stream);
            }

            return new RedirectResponse(
                $this->router->generate('admin_media_list', ['id' => $parentId]),
                Response::HTTP_SEE_OTHER,
            );
        }

        $content = $this->twig->render('@XutimMedia/admin/folder_delete.html.twig', [
            'folder' => $folder,
            'form' => $form->createView(),
        ]);

        return new Response($content);
    }
}
