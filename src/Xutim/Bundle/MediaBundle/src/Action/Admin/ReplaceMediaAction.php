<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\MediaBundle\Domain\Event\MediaFileReplacedEvent;
use Xutim\MediaBundle\Form\Admin\ReplaceMediaType;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Service\MediaUploader;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

final class ReplaceMediaAction
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly MediaUploader $uploader,
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

        $form = $this->formFactory->create(ReplaceMediaType::class, null, [
            'action' => $this->router->generate('admin_media_replace', ['id' => $media->id()]),
            'allowed_extension' => $media->originalExt(),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{file: UploadedFile} $data */
            $data = $form->getData();

            $this->uploader->replace($media, $data['file']);

            $event = new MediaFileReplacedEvent($media->id(), $media->mime(), $media->sizeBytes());
            $logEntry = $this->logEventFactory->create(
                $media->id(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                $this->mediaClass,
                $event,
            );
            $this->logEventRepository->save($logEntry, true);

            $session = $request->getSession();
            Assert::isInstanceOf($session, FlashBagAwareSessionInterface::class);
            $session->getFlashBag()->add('success', 'flash.changes_made_successfully');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                $session->getFlashBag()->add('stream', '<turbo-stream action="refresh"></turbo-stream>');
            }

            $fallbackUrl = $this->router->generate('admin_media_edit', [
                'id' => $media->id(),
            ]);

            return new RedirectResponse($request->headers->get('referer', $fallbackUrl));
        }

        $content = $this->twig->render('@XutimMedia/admin/replace_media.html.twig', [
            'form' => $form->createView(),
            'media' => $media,
        ]);

        return new Response($content);
    }
}
