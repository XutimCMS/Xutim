<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Domain\Factory\MediaFolderFactory;
use Xutim\CoreBundle\Form\Admin\MediaFolderType;
use Xutim\CoreBundle\Repository\MediaFolderRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Security\UserRoles;

class CreateMediaFolderAction extends AbstractController
{
    public function __construct(
        private readonly AdminUrlGenerator $router,
        private readonly MediaFolderFactory $factory,
        private readonly MediaFolderRepository $repo
    ) {
    }

    public function __invoke(Request $request, ?string $id): Response
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $parent = null;
        if ($id !== null) {
            $parent = $this->repo->find($id);
            if ($parent === null) {
                throw $this->createNotFoundException('The media folder does not exist');
            }
        }
        $form = $this->createForm(MediaFolderType::class, null, [
            'action' => $this->router->generate('admin_media_folder_new', [
                'id' => $id
            ])
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{name: string} $data */
            $data = $form->getData();
            $folder = $this->factory->create($data['name'], $parent);
            $this->repo->save($folder, true);

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/media/media_folder_new.html.twig', 'stream_success', [
                    'folder' => $folder
                ]);

                $this->addFlash('stream', $stream);
            }

            return new RedirectResponse($this->router->generate('admin_media_list'), Response::HTTP_SEE_OTHER);
        }

        return $this->render('@XutimCore/admin/media/media_folder_new.html.twig', [
            'form' => $form
        ]);
    }
}
