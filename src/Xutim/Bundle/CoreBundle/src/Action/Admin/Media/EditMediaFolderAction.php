<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Domain\Model\MediaFolderInterface;
use Xutim\CoreBundle\Form\Admin\MediaFolderType;
use Xutim\CoreBundle\Repository\MediaFolderRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Security\UserRoles;

class EditMediaFolderAction extends AbstractController
{
    public function __construct(
        private readonly AdminUrlGenerator $router,
        private readonly MediaFolderRepository $repo
    ) {
    }

    public function __invoke(Request $request, MediaFolderInterface $folder): Response
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $form = $this->createForm(MediaFolderType::class, ['name' => $folder->getName()], [
            'action' => $this->router->generate('admin_media_folder_edit', ['id' => $folder->getId()])
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{name: string} $data */
            $data = $form->getData();
            $folder->change($data['name'], $folder->getParent());
            $this->repo->save($folder, true);

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/media/media_folder_edit.html.twig', 'stream_success', [
                    'folder' => $folder
                ]);

                $this->addFlash('stream', $stream);
            }

            $redirectId = $folder->getParent()?->getId();
            return new RedirectResponse(
                $this->router->generate('admin_media_list', ['id' => $redirectId]),
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('@XutimCore/admin/media/media_folder_edit.html.twig', [
            'form' => $form,
            'folder' => $folder
        ]);
    }
}
