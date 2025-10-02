<?php

declare(strict_types=1);
namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;

class JsonEditorJSDataAction extends AbstractController
{
    public function __construct(
        private readonly ContentTranslationRepository $repo
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $trans = $this->repo->find($id);
        if ($trans === null) {
            throw new NotFoundHttpException('The content translation does not exist');
        }
        $meta = [
            'meta' => [
                'pretitle' => $trans->getPreTitle(),
                'title' => $trans->getTitle(),
                'subtitle' => $trans->getSubTitle(),
                'slug' => $trans->getSlug(),
                'description' => $trans->getDescription()
            ]
        ];

        return new JsonResponse(array_merge($meta, $trans->getContent()));
    }
}
