<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Tag;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Repository\TagTranslationRepository;

class JsonGenerateSlugAction extends AbstractController
{
    public function __construct(private readonly TagTranslationRepository $tagTransRepo)
    {
    }
    public function __invoke(Request $request): Response
    {
        $title = $request->query->get('title', '');
        $locale = $request->query->get('locale', $request->getLocale());
        $selfId = $request->query->getString('selfId');
        $existing = $selfId !== '' ? $this->tagTransRepo->find($selfId) : null;
        $slug = $this->tagTransRepo->generateUniqueSlugForName($title, $locale, $existing);

        return $this->json($slug);
    }
}
