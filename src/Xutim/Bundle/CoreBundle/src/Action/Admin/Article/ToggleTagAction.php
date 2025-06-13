<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\UX\Turbo\TurboBundle;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Service\SearchContentBuilder;

#[Route('/article/toggle-tag/{id}/{tagId}', name: 'admin_article_toggle_tag', methods: ['get', 'post'])]
class ToggleTagAction extends AbstractController
{
    public const string TOKEN_NAME = 'toggle-tag';

    public function __construct(
        private readonly ArticleRepository $articleRepo,
        private readonly TagRepository $tagRepo,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly SearchContentBuilder $searchContentBuilder,
    ) {
    }

    public function __invoke(Request $request, string $id, string $tagId): Response
    {
        $article = $this->articleRepo->find($id);
        if ($article === null) {
            throw $this->createNotFoundException('The article does not exist');
        }
        $tag = $this->tagRepo->find($tagId);
        if ($tag === null) {
            throw $this->createNotFoundException('The tag does not exist');
        }
        $submittedToken = $request->headers->get('X-CSRF-TOKEN', '');
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken(self::TOKEN_NAME, $submittedToken))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }
        $article->hasTag($tag);
        if ($article->hasTag($tag) === true) {
            $article->removeTag($tag);
        } else {
            $article->addTag($tag);
        }
        foreach ($article->getTranslations() as $trans) {
            $searchTagContent = $this->searchContentBuilder->buildTagContent($trans);
            $trans->changeSearchTagContent($searchTagContent);
        }

        $this->articleRepo->save($article, true);

        $this->addFlash('success', 'flash.changes_made_successfully');

        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->renderBlock('@XutimCore/admin/article/article_edit_tags.html.twig', 'stream_success', [
                'article' => $article
            ]);
        }

        $fallbackUrl = $this->generateUrl('admin_article_edit', [
            'id' => $article->getId()
        ]);

        return $this->redirect($request->headers->get('referer', $fallbackUrl));
    }
}
