<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\ContentTranslation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationCreatedEvent;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationUpdatedEvent;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Service\EditorJsDiffRenderer;

class ShowReferenceDiffAction extends AbstractController
{
    public function __construct(
        private readonly ContentTranslationRepository $contentTransRepo,
        private readonly LogEventRepository $logEventRepo,
        private readonly EditorJsDiffRenderer $diffRenderer,
        private readonly SiteContext $siteContext,
    ) {
    }

    public function __invoke(string $id): Response
    {
        $translation = $this->contentTransRepo->find($id);
        if ($translation === null) {
            throw $this->createNotFoundException('Translation not found');
        }

        $snapshotAt = $translation->getReferenceSyncedAt();
        if ($snapshotAt === null) {
            return $this->render('@XutimCore/admin/content_translation/_reference_diff_empty.html.twig', [
                'reason' => 'no_snapshot',
            ]);
        }

        $refLocale = $this->siteContext->getReferenceLocale();
        $refTranslation = $translation->getObject()->getTranslationByLocale($refLocale);
        if ($refTranslation === null) {
            return $this->render('@XutimCore/admin/content_translation/_reference_diff_empty.html.twig', [
                'reason' => 'no_reference',
            ]);
        }

        $oldRevision = $this->logEventRepo->findRevisionAtOrBefore($refTranslation, $snapshotAt);
        if ($oldRevision === null) {
            return $this->render('@XutimCore/admin/content_translation/_reference_diff_empty.html.twig', [
                'reason' => 'no_history',
            ]);
        }

        /** @var ContentTranslationCreatedEvent|ContentTranslationUpdatedEvent $oldEvent */
        $oldEvent = $oldRevision->getEvent();

        $currentRevision = $this->logEventRepo->findLatestContentRevision($refTranslation);

        if ($currentRevision !== null) {
            /** @var ContentTranslationCreatedEvent|ContentTranslationUpdatedEvent $newEvent */
            $newEvent = $currentRevision->getEvent();
            $newPreTitle = $newEvent->preTitle;
            $newTitle = $newEvent->title;
            $newSubTitle = $newEvent->subTitle;
            $newDescription = $newEvent->description;
            $newContent = $newEvent->content;
        } else {
            $newPreTitle = $refTranslation->getPreTitle();
            $newTitle = $refTranslation->getTitle();
            $newSubTitle = $refTranslation->getSubTitle();
            $newDescription = $refTranslation->getDescription();
            $newContent = $refTranslation->getContent();
        }

        $preTitleDiff = $this->diffRenderer->diffTitle($oldEvent->preTitle, $newPreTitle);
        $titleDiff = $this->diffRenderer->diffTitle($oldEvent->title, $newTitle);
        $subTitleDiff = $this->diffRenderer->diffTitle($oldEvent->subTitle, $newSubTitle);
        $descriptionDiff = $this->diffRenderer->diffDescription($oldEvent->description, $newDescription);
        $contentRows = $this->diffRenderer->diffContent($oldEvent->content, $newContent);

        return $this->render('@XutimCore/admin/content_translation/_reference_diff.html.twig', [
            'preTitleDiff' => $preTitleDiff,
            'titleDiff' => $titleDiff,
            'subTitleDiff' => $subTitleDiff,
            'descriptionDiff' => $descriptionDiff,
            'contentRows' => $contentRows,
            'oldRevision' => $oldRevision,
            'referenceTranslation' => $refTranslation,
        ]);
    }
}
