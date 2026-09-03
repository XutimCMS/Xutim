<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\ContentTranslation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;

class ShowReferenceChangesAction extends AbstractController
{
    public function __construct(
        private readonly ContentTranslationRepository $contentTransRepo,
        private readonly LogEventRepository $logEventRepo,
        private readonly SiteContext $siteContext,
    ) {
    }

    public function __invoke(string $id): RedirectResponse
    {
        $translation = $this->contentTransRepo->find($id);
        if ($translation === null) {
            throw $this->createNotFoundException('The content translation does not exist');
        }

        $refLocale = $this->siteContext->getReferenceLocale();
        $reference = $translation->getObject()->getTranslationByLocale($refLocale);
        if ($reference === null) {
            throw $this->createNotFoundException('The reference translation does not exist');
        }

        $params = ['id' => $reference->getId(), '_content_locale' => $refLocale];

        $syncedAt = $translation->getReferenceSyncedAt();
        $oldRevision = $syncedAt === null ? null : $this->logEventRepo->findRevisionAtOrBefore($reference, $syncedAt);
        $newRevision = $this->logEventRepo->findLatestContentRevision($reference);
        if ($oldRevision !== null && $newRevision !== null && !$oldRevision->getId()->equals($newRevision->getId())) {
            $params['oldId'] = $oldRevision->getId();
            $params['newId'] = $newRevision->getId();
        }

        return $this->redirectToRoute('admin_content_translation_revisions', $params);
    }
}
