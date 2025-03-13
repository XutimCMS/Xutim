<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\FileOrMediaType;
use Xutim\CoreBundle\Message\Command\File\UploadFileMessage;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Security\UserStorage;

class AddFileToObjectAction extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserStorage $userStorage,
        private readonly MessageBusInterface $commandBus,
        private readonly FileRepository $fileRepository,
    ) {
    }

    #[Route('/article/media/add/{id}', name: 'admin_article_media_add')]
    public function addToArticle(Request $request, Article $article): Response
    {
        return $this->addToObject($article, $request);
    }

    #[Route('/page/media/add/{id}', name: 'admin_page_media_add')]
    public function addToPage(Request $request, Page $page): Response
    {
        return $this->addToObject($page, $request);
    }

    public function addToObject(Page|Article $object, Request $request): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $form = $this->createForm(FileOrMediaType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{new_file: ?UploadedFile, existing_file: ?File, name: string, alt: ?string, locale: string} $data */
            $data = $form->getData();
            if ($data['existing_file'] !== null) {
                $file = $data['existing_file'];
                $object->addFile($file);
                $file->addObject($object);
                $this->em->flush();

                return new Response(null, 204);
            }
            if ($data['new_file'] !== null) {
                $uploadedFile = $data['new_file'];
                $message = new UploadFileMessage(
                    $uploadedFile,
                    $this->userStorage->getUserWithException()->getUserIdentifier(),
                    $object instanceof Page ? $object : null,
                    $object instanceof Article ? $object : null,
                    $data['name'],
                    $data['alt'] ?? '',
                    $data['locale'],
                );
                $this->commandBus->dispatch($message);

                return new Response(null, 204);
            }
        }

        return $this->render('@XutimCore/admin/media/add_to_object.html.twig', [
            'form' => $form,
            'files' => $this->fileRepository->findAll()
        ]);
    }
}
