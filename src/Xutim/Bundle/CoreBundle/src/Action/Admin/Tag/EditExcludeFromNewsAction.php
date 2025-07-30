<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Tag;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;
use Xutim\SecurityBundle\Security\UserRoles;

class EditExcludeFromNewsAction extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly TagRepository $tagRepo,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $tag = $this->tagRepo->find($id);
        if ($tag === null) {
            throw $this->createNotFoundException('The tag does not exist');
        }
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
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

        $fallbackUrl = $this->router->generate('admin_tag_edit', [
            'id' => $tag->getId()
        ]);

        return $this->redirect($request->headers->get('referer', $fallbackUrl));
    }
}
