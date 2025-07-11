<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Action;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SnippetBundle\Context\SnippetsContext;
use Xutim\SnippetBundle\Domain\Factory\SnippetTranslationFactoryInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetTranslationRepositoryInterface;

class TranslateSnippetAction
{
    public function __construct(
        private readonly SnippetRepositoryInterface $repo,
        private readonly SnippetTranslationRepositoryInterface $transRepo,
        private readonly SnippetsContext $snippetsContext,
        private readonly BlockContext $blockContext,
        private readonly SiteContext $siteContext,
        private readonly SnippetTranslationFactoryInterface $snippetTransFactory,
        private readonly TranslatorAuthChecker $transAuthChecker,
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly string $snippetVersionPath,
    ) {
    }


    public function __invoke(Request $request): Response
    {
        /** @var array{id: string|null, locale: string|null, value: string|null} $data */
        $data = json_decode($request->getContent(), true);
        $id = $data['id'] ?? null;
        Assert::string($id);
        $locale = $data['locale'] ?? null;
        Assert::string($locale);
        $value = $data['value'] ?? null;
        Assert::string($value);

        $this->transAuthChecker->denyUnlessCanTranslate($locale);

        $snippet = $this->repo->findById($id);
        if ($snippet === null) {
            throw new NotFoundHttpException('The snippet does not exist');
        }

        $trans = $snippet->getTranslationByLocale($locale);
        if ($trans === null) {
            $trans = $this->snippetTransFactory->create($snippet, $locale, $value);
        } else {
            $trans->update($value);
        }

        $tokenId = sprintf('translate_snippet_%s_%s', $id, $locale);
        $this->csrfTokenChecker->checkTokenFromRequest($tokenId, $request);
        $this->transRepo->save($trans, true);

        $this->snippetsContext->resetSnippet($snippet->getCode());
        $this->blockContext->resetBlocksBelongsToSnippet($snippet);
        $this->siteContext->resetMenu();
        if ($snippet->isRouteType() === true) {
            // Restart the snippet_routes router cache. See
            // CustomRouteLoader for more information
            file_put_contents($this->snippetVersionPath, microtime());
        }

        return new JsonResponse(['status' => 'ok']);
    }
}
