<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\Page;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Event\Page\PageTranslationLocalesUpdatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Message\Command\Page\EditPageTranslationLocalesCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Service\TranslatorNotificationService;

readonly class EditPageTranslationLocalesHandler implements CommandHandlerInterface
{
    public function __construct(
        private PageRepository $pageRepository,
        private LogEventRepository $eventRepository,
        private LogEventFactory $logEventFactory,
        private TranslatorNotificationService $translatorNotificationService,
        private SiteContext $siteContext,
    ) {
    }

    public function __invoke(EditPageTranslationLocalesCommand $cmd): void
    {
        $page = $this->pageRepository->find($cmd->pageId);
        if ($page === null) {
            throw new NotFoundHttpException('Page could not be found.');
        }

        $existingLocales = $page->getTranslationLocales();
        $page->changeAllTranslationLocales($cmd->allTranslationLocales);
        $page->changeTranslationLocales($cmd->translationLocales);
        $this->pageRepository->save($page, true);

        $targetLocales = $cmd->allTranslationLocales ? $this->siteContext->getLocales() : $cmd->translationLocales;
        $addedLocales = array_values(array_diff($targetLocales, $existingLocales));
        $this->translatorNotificationService->notifyNewTranslationLocales(
            $page,
            $addedLocales,
            $cmd->userIdentifier,
        );

        $event = new PageTranslationLocalesUpdatedEvent(
            $page->getId(),
            $cmd->translationLocales,
            $page->getUpdatedAt(),
        );

        $logEntry = $this->logEventFactory->create(
            $page->getId(),
            $cmd->userIdentifier,
            Page::class,
            $event,
        );

        $this->eventRepository->save($logEntry, true);
    }
}
