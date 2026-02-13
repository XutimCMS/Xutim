<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\ContentDraftInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Dto\Admin\ContentTranslation\ContentTranslationDto;
use Xutim\CoreBundle\Entity\PublicationStatus;
use Xutim\CoreBundle\Form\Admin\ContentTranslationType;
use Xutim\CoreBundle\Message\Command\ContentDraft\PublishContentDraftCommand;
use Xutim\CoreBundle\Message\Command\ContentTranslation\CreateContentTranslationCommand;
use Xutim\CoreBundle\Message\Command\ContentTranslation\EditContentTranslationCommand;
use Xutim\CoreBundle\Message\Command\PublicationStatus\ChangePublicationStatusCommand;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\ContentDraftRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Domain\Model\UserInterface;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SecurityBundle\Service\UserStorage;

class EditArticleAction extends AbstractController
{
    public function __construct(
        private readonly SiteContext $siteContext,
        private readonly ContentTranslationRepository $contentTransRepo,
        private readonly ArticleRepository $articleRepo,
        private readonly UserStorage $userStorage,
        private readonly MessageBusInterface $commandBus,
        private readonly ContentContext $contentContext,
        private readonly TranslatorAuthChecker $transAuthChecker,
        private readonly LogEventRepository $eventRepo,
        private readonly TagRepository $tagRepo,
        private readonly AdminUrlGenerator $router,
        private readonly ContentDraftRepository $draftRepo,
    ) {
    }

    public function __invoke(Request $request, string $id, string $locale = ''): Response
    {
        $article = $this->articleRepo->find($id);
        if ($article === null) {
            throw $this->createNotFoundException('The article does not exist');
        }
        $contentLocale = $this->contentContext->getLanguage();
        $translation = $article->getTranslationByLocale($contentLocale);

        $draft = $this->findDraft($translation);

        $form = $this->createTranslationForm($article, $translation, $contentLocale, $locale, $draft);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ContentTranslationDto $data */
            $data = $form->getData();
            $this->transAuthChecker->denyUnlessCanTranslate($data->locale);

            $command = $this->createTranslationCommand($translation, $data, $article);
            $this->commandBus->dispatch($command);

            if ($request->request->get('_save_action') === 'publish') {
                $this->publishAfterSave($translation, $article, $data->locale);
            }

            $this->addFlash('success', 'flash.changes_made_successfully');

            return new RedirectResponse($this->router->generate('admin_article_edit', ['id' => $article->getId()]));
        }

        if ($this->isGranted(UserRoles::ROLE_ADMIN) === false && $this->isGranted(UserRoles::ROLE_TRANSLATOR)) {
            /** @var UserInterface $user */
            $user = $this->getUser();
            $locales = $user->getTranslationLocales();
            $totalTranslations = count($locales);
        } else {
            $locales = null;
            $totalTranslations = count($this->siteContext->getLocales());
        }
        $translatedArticles = $this->articleRepo->countTranslatedTranslations($article, $locales);


        $revisionsCount = $translation === null ? 0 : $this->eventRepo->eventsCountPerTranslation($translation);
        $lastRevision = $translation === null ? null : $this->eventRepo->findLastByTranslation($translation);

        $currentUser = $this->userStorage->getUser();
        $editingUser = ($translation !== null && $translation->isPublished() && $translation->isBeingEditedBy($currentUser))
            ? $translation->getEditingUser()
            : null;

        $refLocale = $this->siteContext->getReferenceLocale();
        $referenceTranslation = $article->getTranslationByLocale($refLocale);

        $referenceHasChanged = false;
        if ($translation !== null && $referenceTranslation !== null
            && $translation->getLocale() !== $refLocale
            && $translation->getReferenceSyncedAt() !== null
        ) {
            $referenceHasChanged = $referenceTranslation->getUpdatedAt() > $translation->getReferenceSyncedAt();
        }

        return $this->render('@XutimCore/admin/article/article_edit.html.twig', [
            'form' => $form,
            'draft' => $draft,
            'editingUser' => $editingUser,
            'revisionsCount' => $revisionsCount,
            'lastRevision' => $lastRevision,
            'article' => $article,
            'translation' => $translation,
            'totalTranslations' => $totalTranslations,
            'translatedTranslations' => $translatedArticles,
            'allTags' => $this->tagRepo->findAllSorted($contentLocale),
            'referenceTranslation' => $referenceTranslation,
            'referenceLocale' => $refLocale,
            'referenceExists' => $referenceTranslation !== null,
            'referenceHasChanged' => $referenceHasChanged,
        ]);
    }

    private function findDraft(?ContentTranslationInterface $translation): ?ContentDraftInterface
    {
        if ($translation === null || !$translation->isPublished()) {
            return null;
        }

        return $this->draftRepo->findDraft($translation);
    }

    /**
     * @return FormInterface<ContentTranslationDto>
     */
    private function createTranslationForm(ArticleInterface $article, ?ContentTranslationInterface $translation, string $contentLocale, string $locale, ?ContentDraftInterface $draft = null): FormInterface
    {
        $existingTranslation = $translation;
        if (strlen(trim($locale)) > 0) {
            $translation = $this->contentTransRepo->findOneBy(['article' => $article, 'locale' => $locale]);
            if ($translation === null) {
                throw new NotFoundHttpException('There is no translation with "' . $locale . '" language.');
            }

            $data = new ContentTranslationDto(
                $translation->getPreTitle(),
                $translation->getTitle(),
                $translation->getSubTitle(),
                $translation->getSlug(),
                $translation->getContent(),
                $translation->getDescription(),
                $contentLocale
            );
        } elseif ($translation === null) {
            $data = new ContentTranslationDto('', '', '', '', [], '', $contentLocale);
        } elseif ($draft !== null) {
            $data = new ContentTranslationDto(
                $draft->getPreTitle(),
                $draft->getTitle(),
                $draft->getSubTitle(),
                $draft->getSlug(),
                $draft->getContent(),
                $draft->getDescription(),
                $translation->getLocale(),
            );
        } else {
            $data = ContentTranslationDto::fromTranslation($translation);
        }

        return $this->createForm(ContentTranslationType::class, $data, [
            'disabled' => $this->transAuthChecker->canTranslate($data->locale) === false,
            'existing_translation' => $existingTranslation
        ]);
    }

    private function publishAfterSave(?ContentTranslationInterface $translation, ArticleInterface $article, string $locale): void
    {
        $userIdentifier = $this->userStorage->getUserWithException()->getUserIdentifier();

        if ($translation === null) {
            $newTranslation = $this->contentTransRepo->findOneBy(['article' => $article, 'locale' => $locale]);
            if ($newTranslation !== null) {
                $this->commandBus->dispatch(new ChangePublicationStatusCommand(
                    $newTranslation->getId(),
                    PublicationStatus::Published,
                    $userIdentifier,
                ));
            }
        } elseif (!$translation->isPublished()) {
            $this->commandBus->dispatch(new ChangePublicationStatusCommand(
                $translation->getId(),
                PublicationStatus::Published,
                $userIdentifier,
            ));
        } else {
            $draft = $this->draftRepo->findDraft($translation);
            if ($draft !== null) {
                $this->commandBus->dispatch(new PublishContentDraftCommand(
                    $draft->getId(),
                    $userIdentifier,
                ));
            }
        }
    }

    private function createTranslationCommand(?ContentTranslationInterface $translation, ContentTranslationDto $data, ArticleInterface $article): CreateContentTranslationCommand|EditContentTranslationCommand
    {
        if ($translation === null) {
            return CreateContentTranslationCommand::fromDto(
                $data,
                null,
                $article->getId(),
                $this->userStorage->getUserWithException()->getUserIdentifier()
            );
        }

        return EditContentTranslationCommand::fromDto(
            $data,
            $translation->getId(),
            $this->userStorage->getUserWithException()->getUserIdentifier()
        );
    }
}
