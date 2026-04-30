<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\ContentTranslation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Exception\CannotDeleteHomepageException;
use Xutim\CoreBundle\Exception\LogicException;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\CoreBundle\Service\ContentTranslationService;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;

class DeleteTranslationAction extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly TranslatorAuthChecker $transAuthChecker,
        private readonly ContentTranslationService $contentTranslationService,
        private readonly ContentTranslationRepository $contentTranslationRepo,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $trans = $this->contentTranslationRepo->find($id);
        if ($trans === null) {
            throw $this->createNotFoundException('The content translation does not exist');
        }
        $this->csrfTokenChecker->checkTokenFromFormRequest('xutim-dialog', $request);
        $object = $trans->getObject();
        $applyToAll = $request->request->getBoolean('apply_to_all');

        if ($applyToAll === true) {
            $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
            foreach ($object->getTranslations() as $sibling) {
                $this->transAuthChecker->denyUnlessCanTranslate($sibling->getLocale());
            }
        } else {
            $this->transAuthChecker->denyUnlessCanTranslate($trans->getLocale());
        }

        // Last translation is about to be deleted.
        $fullyDeleted = $applyToAll || $object->getTranslations()->count() === 1;


        if ($trans->hasArticle()) {
            $deleted = $applyToAll
                ? $this->contentTranslationService->deleteArticle($trans->getArticle())
                : $this->contentTranslationService->deleteTranslation($trans);
            if ($deleted === false) {
                $this->addFlash('danger', 'The article cannot be removed. It has connections to block items or it is part of the menu.');

                return new RedirectResponse($this->router->generate('admin_article_show', ['id' => $object->getId()]));
            }

            if ($fullyDeleted) {
                return new RedirectResponse($this->router->generate('admin_article_list'));
            }

            return new RedirectResponse($this->router->generate('admin_article_show', ['id' => $object->getId()]));
        }

        if ($trans->hasPage()) {
            $pageParent = $trans->getPage()->getParent();
            try {
                $deleted = $applyToAll
                    ? $this->contentTranslationService->deletePage($trans->getPage())
                    : $this->contentTranslationService->deleteTranslation($trans);
            } catch (CannotDeleteHomepageException) {
                $this->addFlash('danger', 'The page can\'t be removed because it is configured as the site homepage.');

                return new RedirectResponse($this->router->generate('admin_page_edit', ['id' => $trans->getPage()->getId()]));
            }
            if ($deleted === false) {
                $this->addFlash('danger', 'The page can\'t be removed. It has either sub-pages, connection to a block item or it is part of the menu.');

                return new RedirectResponse($this->router->generate('admin_page_edit', ['id' => $trans->getPage()->getId()]));
            }

            if ($fullyDeleted) {
                if ($pageParent === null) {
                    return new RedirectResponse($this->router->generate('admin_page_list'));
                } else {
                    return new RedirectResponse($this->router->generate('admin_page_list', ['id' => $pageParent->getId()]));
                }
            }

            return new RedirectResponse($this->router->generate('admin_page_edit', ['id' => $trans->getPage()->getId()]));
        }

        throw new LogicException('Content translation should have either article or page.');
    }
}
