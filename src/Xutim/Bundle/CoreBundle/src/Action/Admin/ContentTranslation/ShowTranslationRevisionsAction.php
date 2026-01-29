<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\ContentTranslation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationCreatedEvent;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationUpdatedEvent;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Service\BlockAuthorTracker;
use Xutim\CoreBundle\Service\EditorJsDiffRenderer;
use Xutim\CoreBundle\Service\RevisionChangeSummaryCalculator;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;

class ShowTranslationRevisionsAction extends AbstractController
{
    public function __construct(
        private readonly LogEventRepository $eventRepository,
        private readonly EditorJsDiffRenderer $diffRenderer,
        private readonly UserRepositoryInterface $userRepository,
        private readonly ContentTranslationRepository $contentTransRepo,
        private readonly RevisionChangeSummaryCalculator $summaryCalculator,
        private readonly BlockAuthorTracker $blockAuthorTracker,
        private readonly SiteContext $siteContext,
        private readonly TagRepository $tagRepo,
        private readonly ContentContext $contentContext,
    ) {
    }

    public function __invoke(Request $request, string $id, ?string $oldId = null, ?string $newId = null): Response
    {
        $translation = $this->contentTransRepo->find($id);
        if ($translation === null) {
            throw $this->createNotFoundException('The content translation does not exist');
        }

        if (($oldId === null && $newId !== null) || ($oldId !== null && $newId === null)) {
            throw $this->createNotFoundException('Either both (oldId and newId) should be set or both have to be null.');
        }

        $logEvents = $this->eventRepository->findContentRevisionsByTranslation($translation);
        $logEventsNewestFirst = array_values(array_reverse($logEvents));
        if ($oldId === null && $newId === null) {
            $newRevision = $logEventsNewestFirst[0];
            $oldRevision = $logEventsNewestFirst[count($logEvents) === 1 ? 0 : 1];
        } else {
            $oldRevision = $this->eventRepository->find($oldId);
            $newRevision = $this->eventRepository->find($newId);
            if ($oldRevision === null || $newRevision === null) {
                throw $this->createNotFoundException('The revision with "' . $oldId . '" or "' . $newId . '" does not exist.');
            }
        }

        /** @var ContentTranslationCreatedEvent|ContentTranslationUpdatedEvent $newEvent */
        $newEvent = $newRevision->getEvent();
        /** @var ContentTranslationCreatedEvent|ContentTranslationUpdatedEvent $oldEvent */
        $oldEvent = $oldRevision->getEvent();

        $preTitleDiff = $this->diffRenderer->diffTitle(
            $oldEvent->preTitle,
            $newEvent->preTitle
        );
        $titleDiff = $this->diffRenderer->diffTitle(
            $oldEvent->title,
            $newEvent->title
        );
        $subTitleDiff = $this->diffRenderer->diffTitle(
            $oldEvent->subTitle,
            $newEvent->subTitle
        );
        $descriptionDiff = $this->diffRenderer->diffDescription(
            $oldEvent->description,
            $newEvent->description
        );

        $contentRows = $this->diffRenderer->diffContent(
            $oldEvent->content,
            $newEvent->content,
        );

        $usersData = $this->userRepository->findAllUsersWithAvatars();

        $changeSummary = $this->summaryCalculator->calculate(
            $titleDiff,
            $preTitleDiff,
            $subTitleDiff,
            $descriptionDiff,
            $contentRows,
        );

        $blockAuthors = $this->blockAuthorTracker->getBlockAuthors(
            $logEvents,
            $oldRevision->getId(),
            $newRevision->getId(),
        );

        $revisionData = [
            'translation' => $translation,
            'preTitleDiff' => $preTitleDiff,
            'titleDiff' => $titleDiff,
            'subTitleDiff' => $subTitleDiff,
            'selectedOld' => $oldRevision,
            'selectedNew' => $newRevision,
            'descriptionDiff' => $descriptionDiff,
            'contentRows' => $contentRows,
            'events' => $logEventsNewestFirst,
            'usersData' => $usersData,
            'changeSummary' => $changeSummary,
            'blockAuthors' => $blockAuthors,
        ];

        if ($translation->hasArticle()) {
            return $this->renderArticleRevisions($translation, $revisionData);
        }

        return $this->renderPageRevisions($translation, $revisionData);
    }

    /**
     * @param array<string, mixed> $revisionData
     */
    private function renderArticleRevisions(ContentTranslationInterface $translation, array $revisionData): Response
    {
        $article = $translation->getArticle();
        $refLocale = $this->siteContext->getReferenceLocale();
        $referenceTranslation = $article->getTranslationByLocale($refLocale);
        $contentLocale = $this->contentContext->getLanguage();

        $referenceHasChanged = false;
        if ($referenceTranslation !== null
            && $translation->getLocale() !== $refLocale
            && $translation->getReferenceSyncedAt() !== null
        ) {
            $referenceHasChanged = $referenceTranslation->getUpdatedAt() > $translation->getReferenceSyncedAt();
        }

        $revisionsCount = $this->eventRepository->eventsCountPerTranslation($translation);
        $lastRevision = $this->eventRepository->findLastByTranslation($translation);

        return $this->render('@XutimCore/admin/revision/article_revisions.html.twig', array_merge($revisionData, [
            'article' => $article,
            'referenceTranslation' => $referenceTranslation,
            'referenceLocale' => $refLocale,
            'referenceExists' => $referenceTranslation !== null,
            'referenceHasChanged' => $referenceHasChanged,
            'revisionsCount' => $revisionsCount,
            'lastRevision' => $lastRevision,
            'allTags' => $this->tagRepo->findAllSorted($contentLocale),
        ]));
    }

    /**
     * @param array<string, mixed> $revisionData
     */
    private function renderPageRevisions(ContentTranslationInterface $translation, array $revisionData): Response
    {
        $page = $translation->getPage();
        $refLocale = $this->siteContext->getReferenceLocale();
        $referenceTranslation = $page->getTranslationByLocale($refLocale);

        $referenceHasChanged = false;
        if ($referenceTranslation !== null
            && $translation->getLocale() !== $refLocale
            && $translation->getReferenceSyncedAt() !== null
        ) {
            $referenceHasChanged = $referenceTranslation->getUpdatedAt() > $translation->getReferenceSyncedAt();
        }

        $revisionsCount = $this->eventRepository->eventsCountPerTranslation($translation);
        $lastRevision = $this->eventRepository->findLastByTranslation($translation);

        return $this->render('@XutimCore/admin/revision/page_revisions.html.twig', array_merge($revisionData, [
            'page' => $page,
            'referenceTranslation' => $referenceTranslation,
            'referenceLocale' => $refLocale,
            'referenceExists' => $referenceTranslation !== null,
            'referenceHasChanged' => $referenceHasChanged,
            'revisionsCount' => $revisionsCount,
            'lastRevision' => $lastRevision,
        ]));
    }
}
