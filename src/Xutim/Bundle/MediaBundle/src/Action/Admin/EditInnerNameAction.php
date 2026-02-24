<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\UX\Turbo\TurboBundle;
use Twig\Environment;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\MediaBundle\Domain\Event\MediaInnerNameUpdatedEvent;
use Xutim\MediaBundle\Form\Admin\MediaInnerNameType;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

final class EditInnerNameAction
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
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

        $form = $this->formFactory->create(MediaInnerNameType::class, ['innerName' => $media->innerName()], [
            'action' => $this->router->generate('admin_media_inner_name_edit', ['id' => $media->id()]),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{innerName: string} $data */
            $data = $form->getData();

            $media->changeInnerName($data['innerName']);
            $this->mediaRepository->save($media, true);

            $event = new MediaInnerNameUpdatedEvent($media->id(), $data['innerName']);
            $logEntry = $this->logEventFactory->create(
                $media->id(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                $this->mediaClass,
                $event,
            );
            $this->logEventRepository->save($logEntry, true);

            /** @var FlashBagAwareSessionInterface $session */
            $session = $request->getSession();
            $session->getFlashBag()->add('success', 'flash.changes_made_successfully');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                $stream = $this->twig->load('@XutimMedia/admin/edit_inner_name.html.twig')
                    ->renderBlock('stream_success', ['media' => $media]);
                $session->getFlashBag()->add('stream', $stream);
            }

            $fallbackUrl = $this->router->generate('admin_media_edit', [
                'id' => $media->id(),
            ]);

            return new RedirectResponse($request->headers->get('referer', $fallbackUrl));
        }

        $content = $this->twig->render('@XutimMedia/admin/edit_inner_name.html.twig', [
            'form' => $form->createView(),
            'media' => $media,
        ]);

        return new Response($content);
    }
}
