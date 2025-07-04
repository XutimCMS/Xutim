<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Tag;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Repository\TagTranslationRepository;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;
use Xutim\SecurityBundle\Security\UserRoles;

#[Route('/tag/delete/{id}', name: 'admin_tag_delete')]
class DeleteTagAction extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly TagTranslationRepository $tagTransRepo,
        private readonly TagRepository $tagRepo,
    ) {
    }

    public function __invoke(string $id, Request $request): Response
    {
        $tag = $this->tagRepo->find($id);
        if ($tag === null) {
            throw $this->createNotFoundException('The tag does not exist');
        }

        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);

        if ($tag->getArticles()->isEmpty() === false) {
            $message = $this->generateLinkedWithArticlesMessage($request, $tag);
            $this->addFlash('danger', $message);

            return $this->redirectToRoute('admin_tag_edit', ['id' => $tag->getId()]);
        }

        foreach ($tag->getTranslations() as $trans) {
            $this->tagTransRepo->remove($trans);
        }

        $this->tagRepo->remove($tag, true);

        return $this->redirectToRoute('admin_tag_list');
    }

    private function generateLinkedWithArticlesMessage(Request $request, TagInterface $tag): string
    {
        $message = 'This tag cannot be deleted because it is still assigned to these articles:<br>';
        foreach ($tag->getArticles() as $article) {
            $path = $this->generateUrl('admin_article_show', ['id' => $article->getId()]);
            $articleTrans = $article->getTranslationByLocaleOrAny($request->getLocale());
            $message = sprintf('%s<a href="%s">%s</a><br>', $message, $path, $articleTrans);
        }

        return $message;
    }
}
