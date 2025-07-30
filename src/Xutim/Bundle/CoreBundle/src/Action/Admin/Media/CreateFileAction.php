<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Form\Admin\FileType;
use Xutim\CoreBundle\Message\Command\File\UploadFileMessage;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\CoreBundle\Service\FileService;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

class CreateFileAction extends AbstractController
{
    public function __construct(
        private readonly UserStorage $userStorage,
        private readonly FileService $fileService,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $form = $this->createForm(FileType::class, null, [
            'action' => $this->router->generate('admin_media_new')
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{file: UploadedFile, name: string, alt: string|null, language: string, copyright: ?string} $data */
            $data = $form->getData();

            $command = new UploadFileMessage(
                $data['file'],
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                null,
                null,
                $data['name'],
                $data['alt'] ?? '',
                $data['language'],
                $data['copyright'] ?? '',
            );
            $file = $this->fileService->persistFile($command);

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/media/new.html.twig', 'stream_success', [
                    'file' => $file
                ]);

                $this->addFlash('stream', $stream);
            }

            return new RedirectResponse($this->router->generate('admin_media_list'), Response::HTTP_SEE_OTHER);
        }

        return $this->render('@XutimCore/admin/media/new.html.twig', [
            'form' => $form
        ]);
    }
}
