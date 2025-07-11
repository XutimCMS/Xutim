<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Action;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\SnippetBundle\Domain\Model\SnippetCategory;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;

class JsonListSnippetsAction
{
    public function __construct(
        private readonly SnippetRepositoryInterface $repo
    ) {
    }

    public function __invoke(string $type): JsonResponse
    {
        if (SnippetCategory::isValidCategory($type) === false) {
            throw new NotFoundHttpException('The type does not exist');
        }
        $snippets = $this->repo->findByCategory(SnippetCategory::from($type));

        $data = array_map(fn (SnippetInterface $snippet) => [
            'id' => $snippet->getId()->toRfc4122(),
            'code' => $snippet->getCode()
        ], $snippets);
        
        return new JsonResponse($data);
    }
}
