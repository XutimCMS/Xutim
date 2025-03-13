<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\ContentTranslation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Exception\LogicException;
use Xutim\CoreBundle\Security\TranslatorAuthChecker;
use Xutim\CoreBundle\Service\ContentTranslationService;
use Xutim\CoreBundle\Service\CsrfTokenChecker;

#[Route('/content-translation/delete/{id}', name: 'admin_content_translation_delete')]
class DeleteTranslationAction extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly TranslatorAuthChecker $transAuthChecker,
        private readonly ContentTranslationService $contentTranslationService
    ) {
    }

    public function __invoke(ContentTranslation $trans, Request $request): Response
    {
        $this->transAuthChecker->denyUnlessCanTranslate($trans->getLocale());
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);

        if ($trans->hasArticle()) {
            if ($this->contentTranslationService->deleteTranslation($trans) === false) {
                $this->addFlash('danger', 'The article cannot be removed. It has connections to block items or it is part of the menu.');

                return $this->redirectToRoute('admin_article_show', ['id' => $trans->getArticle()->getId()]);
            }

            return $this->redirectToRoute('admin_article_list');
        }

        if ($trans->hasPage()) {
            if ($this->contentTranslationService->deleteTranslation($trans) === false) {
                $this->addFlash('danger', 'The page can\'t be removed. It has either sub-pages, connection to a block item or it is part of the menu.');

                return $this->redirectToRoute('admin_page_edit', ['id' => $trans->getPage()->getId()]);
            }

            return $this->redirectToRoute('admin_page_list');
        }

        throw new LogicException('Content translation should have either article or page.');
    }
}
