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

        $preTitleDiff = $this->diffRenderer->diffTitle($oldEvent->preTitle, $refTranslation->getPreTitle());
        $titleDiff = $this->diffRenderer->diffTitle($oldEvent->title, $refTranslation->getTitle());
        $subTitleDiff = $this->diffRenderer->diffTitle($oldEvent->subTitle, $refTranslation->getSubTitle());
        $descriptionDiff = $this->diffRenderer->diffDescription($oldEvent->description, $refTranslation->getDescription());
        $contentRows = $this->diffRenderer->diffContent($oldEvent->content, $refTranslation->getContent());

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
