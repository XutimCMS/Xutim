<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Action;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Service\FlashNotifier;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SnippetBundle\Context\SnippetsContext;
use Xutim\SnippetBundle\Domain\Factory\SnippetTranslationFactoryInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetTranslationRepositoryInterface;
use Xutim\SnippetBundle\Form\SnippetFormData;
use Xutim\SnippetBundle\Form\SnippetType;

class EditSnippetAction
{
    public function __construct(
        private readonly SnippetRepositoryInterface $repo,
        private readonly SnippetTranslationRepositoryInterface $transRepo,
        private readonly ContentContext $context,
        private readonly SnippetsContext $snippetsContext,
        private readonly BlockContext $blockContext,
        private readonly SiteContext $siteContext,
        private readonly SnippetTranslationFactoryInterface $snippetTransFactory,
        private readonly TranslatorAuthChecker $transAuthChecker,
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly Environment $twig,
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $router,
        private readonly FlashNotifier $flashNotifier,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $snippetVersionPath,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $locale = $this->context->getLanguage();
        $this->transAuthChecker->denyUnlessCanTranslate($locale);

        $snippet = $this->repo->findById($id);
        if ($snippet === null) {
            throw new NotFoundHttpException('The snippet does not exist');
        }
        $locale = $this->context->getLanguage();

        $form = $this->formFactory->create(SnippetType::class, $snippet->toFormData(), [
            'action' => $this->router->generate('admin_snippet_edit', ['id' => $snippet->getId()]),
            'can_edit' => $this->authChecker->isGranted(UserRoles::ROLE_EDITOR)
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->transAuthChecker->denyUnlessCanTranslate($locale);
            /** @var SnippetFormData $data */
            $data = $form->getData();
            $snippet->change($data->getCode(), $data->getDescription(), $data->getCategory());

            foreach ($data->getContents() as $contentLocale => $content) {
                $trans = $snippet->getTranslationByLocale($contentLocale);
                if ($trans === null) {
                    if ($content === '') {
                        continue;
                    }
                    $trans = $this->snippetTransFactory->create($snippet, $contentLocale, $content);
                    $snippet->addTranslation($trans);
                    $this->transRepo->save($trans);
                    continue;
                }

                $trans->update($content);
            }

            $this->entityManager->flush();
            $this->snippetsContext->resetSnippet($snippet->getCode());
            $this->blockContext->resetBlocksBelongsToSnippet($snippet);
            $this->siteContext->resetMenu();
            if ($snippet->isRouteType() === true) {
                // Restart the snippet_routes router cache. See
                // CustomRouteLoader for more information
                file_put_contents($this->snippetVersionPath, microtime());
            }
            $this->flashNotifier->changesSaved();

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->twig
                    ->load('@XutimSnippet/admin/snippet/edit.html.twig')
                    ->renderBlock('stream_success');

                $this->flashNotifier->stream($stream);
            }

            return new RedirectResponse(
                $this->router->generate('admin_snippet_list', [], Response::HTTP_SEE_OTHER)
            );
        }

        $html = $this->twig->render('@XutimSnippet/admin/snippet/edit.html.twig', [
            'form' => $form->createView(),
            'snippet' => $snippet,
        ]);

        return new Response($html);
    }
}
