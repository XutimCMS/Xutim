<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\FileOrMediaType;
use Xutim\CoreBundle\Message\Command\File\UploadFileMessage;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Security\UserStorage;
use Xutim\CoreBundle\Service\FileService;

class AddHeaderImageToObjectAction extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserStorage $userStorage,
        private readonly FileRepository $fileRepository,
        private readonly FileService $fileService
    ) {
    }

    #[Route('/article/media/add-header-image/{id}', name: 'admin_article_header_image_add')]
    public function __invoke(Request $request, Article $article): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $form = $this->createForm(FileOrMediaType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{new_file: ?UploadedFile, existing_file: ?File, name: string, alt: ?string, locale: string} $data */
            $data = $form->getData();
            // TODO: Check if the file is really an image
            if ($data['existing_file'] !== null) {
                $file = $data['existing_file'];
                // $article->setHeaderImage($file);
                $this->em->flush();

                return new Response(null, 204);
            }
            if ($data['new_file'] !== null) {
                $uploadedFile = $data['new_file'];
                $newFile = $this->fileService->persistFile(new UploadFileMessage(
                    $uploadedFile,
                    $this->userStorage->getUserWithException()->getUserIdentifier(),
                    null,
                    null,
                    $data['name'],
                    $data['alt'] ?? '',
                    $data['locale'],
                ));
                // $article->setHeaderImage($newFile);

                return new Response(null, 204);
            }
        }

        return $this->render('@XutimCore/admin/media/add_to_object.html.twig', [
            'form' => $form,
            'files' => $this->fileRepository->findAll()
        ]);
    }
}
