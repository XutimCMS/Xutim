<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Snippet;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Domain\Model\SnippetInterface;
use Xutim\CoreBundle\Repository\SnippetRepository;
use Xutim\SecurityBundle\Security\UserRoles;

#[Route('/json/snippet/list/{type}', name: 'admin_json_snippet_list', methods: ['get'])]
class JsonListSnippetsAction extends AbstractController
{
    public function __construct(
        private readonly SnippetRepository $repo
    ) {
    }

    public function __invoke(string $type): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_USER);
        if (in_array($type, ['anchor', 'route'], true) === false) {
            throw $this->createNotFoundException('The type does not exist');
        }
        $snippets = $this->repo->findByType($type);

        $data = array_map(fn (SnippetInterface $snippet) => [
            'id' => $snippet->getId()->toRfc4122(),
            'code' => $snippet->getCode()
        ], $snippets);
        
        return $this->json($data);
    }
}
