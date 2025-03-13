<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\Page;

#[Route('/page/{id}/show-translation/{locale}', name: 'admin_page_translation_show')]
class ShowPageTranslationAction extends AbstractController
{
    public function __invoke(Page $page, string $locale): Response
    {
        return $this->render('@XutimCore/admin/page_translation/show.html.twig', [
            'page' => $page,
            'translation' => $page->getTranslationByLocale($locale)
        ]);
    }
}
