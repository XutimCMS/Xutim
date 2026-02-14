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
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\MediaBundle\Domain\Event\MediaFolderUpdatedEvent;
use Xutim\MediaBundle\Form\Admin\MediaFolderType;
use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

final class EditFolderAction
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

        $form = $this->formFactory->create(MediaFolderType::class, ['name' => $folder->name()], [
            'action' => $this->router->generate('admin_media_folder_edit', ['id' => $id]),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{name: string} $data */
            $data = $form->getData();

            $folder->changeName($data['name']);
            $this->folderRepository->save($folder, true);

            $event = new MediaFolderUpdatedEvent($folder->id(), $folder->name());
            $logEntry = $this->logEventFactory->create(
                $folder->id(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                $this->mediaFolderClass,
                $event,
            );
            $this->logEventRepository->save($logEntry, true);

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                $stream = $this->twig->load('@XutimMedia/admin/folder_edit.html.twig')
                    ->renderBlock('stream_success', ['folder' => $folder]);

                /** @var FlashBagInterface $flashBag */
                $flashBag = $request->getSession()->getBag('flashes');
                $flashBag->add('stream', $stream);
            }

            $redirectId = $folder->parent()?->id()->toRfc4122();

            return new RedirectResponse(
                $this->router->generate('admin_media_list', ['id' => $redirectId]),
                Response::HTTP_SEE_OTHER,
            );
        }

        $content = $this->twig->render('@XutimMedia/admin/folder_edit.html.twig', [
            'form' => $form->createView(),
            'folder' => $folder,
        ]);

        return new Response($content);
    }
}
