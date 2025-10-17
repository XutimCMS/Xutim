<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Tag;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Repository\TagRepository;

class JsonListTagsAction extends AbstractController
{
    public function __construct(
        private readonly TagRepository $tagRepo,
        private readonly ContentContext $contentContext
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $locale = $this->contentContext->getLanguage();
        $tags = $this->tagRepo->findAllSorted($locale);

        $data = [];
        foreach ($tags as $tag) {
            $data[$tag->getId()->toRfc4122()] = $tag->getTranslationByLocaleOrAny($locale)->getName();
        }

        return $this->json($data);
    }
}
