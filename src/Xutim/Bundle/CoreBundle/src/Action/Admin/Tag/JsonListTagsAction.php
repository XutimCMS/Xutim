<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Tag;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Xutim\CoreBundle\Repository\TagRepository;

class JsonListTagsAction extends AbstractController
{
    public function __construct(
        private readonly TagRepository $tagRepo
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $tags = $this->tagRepo->findAll();

        $data = [];
        foreach ($tags as $tag) {
            $data[$tag->getId()->toRfc4122()] = $tag->getTranslationByLocaleOrAny($request->getLocale())->getName();
        }

        return $this->json($data);
    }
}
