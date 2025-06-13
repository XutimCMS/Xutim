<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Tag;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Service\CsrfTokenChecker;

#[Route('/tag/exclude-from-news-toggle/{id}', name: 'admin_tag_exclude_from_news_toggle', methods: ['get', 'post'])]
class EditExcludeFromNewsAction extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly TagRepository $tagRepo
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $tag = $this->tagRepo->find($id);
        if ($tag === null) {
            throw $this->createNotFoundException('The tag does not exist');
        }
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);

        $tag->toggleExcludeFromNews();
        $this->tagRepo->save($tag, true);

        $this->addFlash('success', 'flash.changes_made_successfully');

        if ($request->headers->has('turbo-frame')) {
            $stream = $this->renderBlockView('@XutimCore/admin/tag/_exclude_from_news_item.html.twig', 'stream_success', [
                'tag' => $tag
            ]);

            $this->addFlash('stream', $stream);
        }

        $fallbackUrl = $this->generateUrl('admin_tag_edit', [
            'id' => $tag->getId()
        ]);

        return $this->redirect($request->headers->get('referer', $fallbackUrl));
    }
}
