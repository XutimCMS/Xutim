<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\ContentTranslation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;

#[Route('/json/content-translation/generate-slug', name: 'admin_json_content_translation_generate_slug', methods: ['get'])]
class JsonGenerateSlugAction extends AbstractController
{
    public function __construct(private readonly ContentTranslationRepository $contentTransRepo)
    {
    }
    public function __invoke(Request $request): Response
    {
        $title = $request->query->get('title', '');
        $locale = $request->query->get('locale', $request->getLocale());
        $slug = $this->contentTransRepo->generateUniqueSlugForTitle($title, $locale);

        return $this->json($slug);
    }
}
