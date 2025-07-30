<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Domain\Event\File\FileDeletedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Form\Admin\DeleteType;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\FileTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\CoreBundle\Service\FileUploader;
use Xutim\SecurityBundle\Service\UserStorage;

class DeleteFileAction extends AbstractController
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private readonly FileRepository $fileRepo,
        private readonly FileTranslationRepository $fileTransRepo,
        private readonly UserStorage $userStorage,
        private readonly LogEventRepository $eventRepo,
        private readonly FileUploader $fileUploader,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $file = $this->fileRepo->find($id);
        if ($file === null) {
            throw $this->createNotFoundException('The file does not exist');
        }
        $this->denyAccessUnlessGranted('ROLE_EDITOR');
        $form = $this->createForm(DeleteType::class, [], [
            'action' => $this->router->generate('admin_media_delete', ['id' => $file->getId()]),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file->removeConnections();
            foreach ($file->getTranslations() as $trans) {
                $this->fileTransRepo->remove($trans);
            }

            $fileName = $file->getFileName();
            $id = $file->getId();
            $userIdentifier = $this->userStorage->getUserWithException()->getUserIdentifier();
            $event = new FileDeletedEvent($id);
            $logEntry = $this->logEventFactory->create($id, $userIdentifier, File::class, $event);

            $this->fileRepo->remove($file, true);
            $this->eventRepo->save($logEntry, true);
            $this->fileUploader->deleteFile($fileName);

            return new RedirectResponse($this->router->generate('admin_media_list'));
        }

        return $this->render('@XutimCore/admin/media/delete.html.twig', [
            'file' => $file,
            'form' => $form
        ]);
    }
}
