<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\UX\Turbo\TurboBundle;
use Xutim\CoreBundle\Dto\Admin\Article\ArticleTranslationLocalesDto;
use Xutim\CoreBundle\Form\Admin\ArticleTranslationLocalesType;
use Xutim\CoreBundle\Message\Command\Article\EditArticleTranslationLocalesCommand;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

class EditArticleTranslationLocalesAction extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserStorage $userStorage,
        private readonly ArticleRepository $articleRepo,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $article = $this->articleRepo->find($id);
        if ($article === null) {
            throw $this->createNotFoundException('The article does not exist');
        }
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);

        $existingTranslationLocales = $article->getTranslations()->map(fn ($t) => $t->getLocale())->toArray();
        $form = $this->createForm(ArticleTranslationLocalesType::class, ArticleTranslationLocalesDto::fromArticle($article), [
            'action' => $this->router->generate('admin_article_translation_locales_edit', ['id' => $article->getId()]),
            'existing_translation_locales' => array_values($existingTranslationLocales),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ArticleTranslationLocalesDto $dto */
            $dto = $form->getData();
            $command = EditArticleTranslationLocalesCommand::fromDto(
                $dto,
                $article->getId(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
            );
            $this->commandBus->dispatch($command);

            $this->addFlash('success', 'flash.changes_made_successfully');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $stream = $this->renderBlockView('@XutimCore/admin/article/article_edit_translation_locales.html.twig', 'stream_success', [
                    'article' => $article,
                ]);
                $this->addFlash('stream', $stream);
            }

            $fallbackUrl = $this->router->generate('admin_article_edit', [
                'id' => $article->getId(),
            ]);

            return $this->redirect($request->headers->get('referer', $fallbackUrl), 302);
        }

        return $this->render('@XutimCore/admin/article/article_edit_translation_locales.html.twig', [
            'form' => $form,
            'article' => $article,
        ]);
    }
}
