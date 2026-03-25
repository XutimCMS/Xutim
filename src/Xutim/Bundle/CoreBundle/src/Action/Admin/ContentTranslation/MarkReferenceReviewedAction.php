<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\ContentTranslation;

use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;

class MarkReferenceReviewedAction extends AbstractController
{
    public function __construct(
        private readonly ContentTranslationRepository $translationRepo,
        private readonly SiteContext $siteContext,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $translation = $this->translationRepo->find($id);
        if ($translation === null) {
            throw $this->createNotFoundException('Content translation not found');
        }

        $refLocale = $this->siteContext->getReferenceLocale();
        if ($translation->getLocale() === $refLocale) {
            return $this->json(['error' => 'Cannot mark reference translation as reviewed'], 400);
        }

        $object = $translation->getObject();
        $refTranslation = $object->getTranslationByLocale($refLocale);
        $syncedAt = $refTranslation !== null ? $refTranslation->getUpdatedAt() : new DateTimeImmutable();

        $translation->changeReferenceSyncedAt($syncedAt);
        $this->translationRepo->save($translation, true);

        return $this->json(['ok' => true]);
    }
}
