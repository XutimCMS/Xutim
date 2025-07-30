<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Domain\Factory\FileTranslationFactory;
use Xutim\CoreBundle\Entity\FileTranslation;
use Xutim\CoreBundle\File\FileInfoService;
use Xutim\CoreBundle\Form\Admin\FileType;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\FileTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;

class EditFileAction extends AbstractController
{
    public function __construct(
        private readonly FileTranslationRepository $fileTranslationRepository,
        private readonly FileRepository $fileRepo,
        private readonly LogEventRepository $eventRepo,
        private readonly ContentContext $context,
        private readonly TranslatorAuthChecker $transAuthChecker,
        private readonly BlockContext $blockContext,
        private readonly FileTranslationFactory $fileTranslationFactory,
        private readonly FileInfoService $imageInfoService,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $file = $this->fileRepo->find($id);
        if ($file === null) {
            throw $this->createNotFoundException('The file does not exist');
        }
        $locale = $this->context->getLanguage();
        /** @var null|FileTranslation $translation */
        $translation = $file->getTranslationByLocale($locale);
        $form = $this->createForm(FileType::class, [
            'name' => $translation?->getName() ?? '',
            'alt' => $translation?->getAlt() ?? '',
        ], [
            'disabled' => $this->transAuthChecker->canTranslate($locale) === false
        ]);
        $form->remove('file');
        $form->remove('language');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->transAuthChecker->denyUnlessCanTranslate($locale);
            /** @var array{name: string, alt: null|string} $data */
            $data = $form->getData();

            if ($translation === null) {
                $translation = $this->fileTranslationFactory->create($locale, $data['name'], $data['alt'] ?? '', $file);
            } else {
                $translation->update($data['name'], $data['alt'] ?? '');
            }
            $this->blockContext->resetBlocksBelongsToFile($file);

            $this->addFlash('success', 'Changes were made successfully.');
            $this->fileTranslationRepository->save($translation, true);
            /** @var string $referer */
            $referer = $request->headers->get('referer', $this->router->generate('admin_media_list'));

            return $this->redirect($referer);
        }

        $firstRev = $this->eventRepo->findFirstByObject($file);
        $imageInfo = null;
        if ($file->isImage() === true) {
            $imageInfo = $this->imageInfoService->getImageInfo($file);
        }
        $fileInfo = $this->imageInfoService->getFileInfo($file);

        return $this->render('@XutimCore/admin/media/edit.html.twig', [
            'file' => $file,
            'translation' => $translation,
            'form' => $form,
            'firstRev' => $firstRev,
            'imageInfo' => $imageInfo,
            'fileInfo' => $fileInfo
        ]);
    }
}
