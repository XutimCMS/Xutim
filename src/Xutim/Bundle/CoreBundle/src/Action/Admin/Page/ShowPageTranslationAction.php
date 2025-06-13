<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Repository\PageRepository;

#[Route('/page/{id}/show-translation/{locale}', name: 'admin_page_translation_show')]
class ShowPageTranslationAction extends AbstractController
{
    public function __construct(private readonly PageRepository $pageRepo)
    {
    }

    public function __invoke(string $id, string $locale): Response
    {
        $page = $this->pageRepo->find($id);
        if ($page === null) {
            throw $this->createNotFoundException('The page does not exist');
        }
        return $this->render('@XutimCore/admin/page_translation/show.html.twig', [
            'page' => $page,
            'translation' => $page->getTranslationByLocale($locale)
        ]);
    }
}
