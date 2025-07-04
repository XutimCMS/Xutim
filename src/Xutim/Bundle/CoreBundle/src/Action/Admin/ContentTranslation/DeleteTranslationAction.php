<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\ContentTranslation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Exception\LogicException;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Service\ContentTranslationService;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;

#[Route('/content-translation/delete/{id}', name: 'admin_content_translation_delete')]
class DeleteTranslationAction extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly TranslatorAuthChecker $transAuthChecker,
        private readonly ContentTranslationService $contentTranslationService,
        private readonly ContentTranslationRepository $contentTranslationRepo,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $trans = $this->contentTranslationRepo->find($id);
        if ($trans === null) {
            throw $this->createNotFoundException('The content translation does not exist');
        }
        $this->transAuthChecker->denyUnlessCanTranslate($trans->getLocale());
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);
        $object = $trans->getObject();
        // Last translation is about to be deleted.
        $fullyDeleted = $object->getTranslations()->count() === 1;


        if ($trans->hasArticle()) {
            if ($this->contentTranslationService->deleteTranslation($trans) === false) {
                $this->addFlash('danger', 'The article cannot be removed. It has connections to block items or it is part of the menu.');

                return $this->redirectToRoute('admin_article_show', ['id' => $object->getId()]);
            }

            if ($fullyDeleted) {
                return $this->redirectToRoute('admin_article_list');
            }

            return $this->redirectToRoute('admin_article_show', ['id' => $object->getId()]);
        }

        if ($trans->hasPage()) {
            $pageParent = $trans->getPage()->getParent();
            if ($this->contentTranslationService->deleteTranslation($trans) === false) {
                $this->addFlash('danger', 'The page can\'t be removed. It has either sub-pages, connection to a block item or it is part of the menu.');

                return $this->redirectToRoute('admin_page_edit', ['id' => $trans->getPage()->getId()]);
            }

            if ($fullyDeleted) {
                if ($pageParent === null) {
                    return $this->redirectToRoute('admin_page_list');
                } else {
                    return $this->redirectToRoute('admin_page_list', ['id' => $pageParent->getId()]);
                }
            }

            return $this->redirectToRoute('admin_page_edit', ['id' => $trans->getPage()->getId()]);
        }

        throw new LogicException('Content translation should have either article or page.');
    }
}
