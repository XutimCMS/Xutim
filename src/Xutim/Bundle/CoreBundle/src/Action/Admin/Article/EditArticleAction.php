<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Dto\Admin\ContentTranslation\ContentTranslationDto;
use Xutim\CoreBundle\Form\Admin\ContentTranslationType;
use Xutim\CoreBundle\Message\Command\ContentTranslation\CreateContentTranslationCommand;
use Xutim\CoreBundle\Message\Command\ContentTranslation\EditContentTranslationCommand;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\SecurityBundle\Security\UserInterface;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SecurityBundle\Service\UserStorage;

#[Route('/article/edit/{id}/{locale? }', name: 'admin_article_edit', methods: ['get', 'post'])]
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

        $form = $this->createTranslationForm($article, $translation, $contentLocale, $locale);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ContentTranslationDto $data */
            $data = $form->getData();
            $this->transAuthChecker->denyUnlessCanTranslate($data->locale);

            $command = $this->createTranslationCommand($translation, $data, $article);
            $this->commandBus->dispatch($command);

            $this->addFlash('success', 'flash.changes_made_successfully');

            return $this->redirectToRoute('admin_article_edit', ['id' => $article->getId()]);
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

        return $this->render('@XutimCore/admin/article/article_edit.html.twig', [
            'form' => $form,
            'revisionsCount' => $revisionsCount,
            'lastRevision' => $lastRevision,
            'article' => $article,
            'translation' => $translation,
            'totalTranslations' => $totalTranslations,
            'translatedTranslations' => $translatedArticles,
            'allTags' => $this->tagRepo->findAll()
        ]);
    }

    /**
     * @return FormInterface<ContentTranslationDto>
     */
    private function createTranslationForm(ArticleInterface $article, ?ContentTranslationInterface $translation, string $contentLocale, string $locale): FormInterface
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
        } else {
            $data = ContentTranslationDto::fromTranslation($translation);
        }

        return $this->createForm(ContentTranslationType::class, $data, [
            'disabled' => $this->transAuthChecker->canTranslate($data->locale) === false,
            'existing_translation' => $existingTranslation
        ]);
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
