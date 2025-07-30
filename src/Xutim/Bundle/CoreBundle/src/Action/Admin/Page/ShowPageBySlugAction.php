<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;

class ShowPageBySlugAction extends AbstractController
{
    public function __construct(
        private readonly ContentTranslationRepository $contentTransRepo,
    ) {
    }

    public function __invoke(Request $request, string $slug): Response
    {
        $trans = $this->contentTransRepo->findOneBy(['slug' => $slug, 'locale' => $request->getLocale()]);
        if ($trans === null) {
            throw $this->createNotFoundException(sprintf('The page translation with a slug %s was not found.', $slug));
        }

        return $this->forward(
            ListPagesAction::class,
            ['id' => $trans->getPage()->getId()]
        );
    }
}
