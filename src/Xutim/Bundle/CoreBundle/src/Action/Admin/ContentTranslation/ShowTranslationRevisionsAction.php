<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\ContentTranslation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationCreatedEvent;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationUpdatedEvent;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\LogEventInterface;
use Xutim\CoreBundle\Form\Admin\RevisionListType;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Service\ContentFragmentsConverter;
use Xutim\CoreBundle\Service\TextDiff;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;

class ShowTranslationRevisionsAction extends AbstractController
{
    public function __construct(
        private readonly LogEventRepository $eventRepository,
        private readonly TextDiff $textDiff,
        private readonly ContentFragmentsConverter $fragmentsConverter,
        private readonly UserRepositoryInterface $userRepository,
        private readonly ContentTranslationRepository $contentTransRepo,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $translation = $this->contentTransRepo->find($id);
        if ($translation === null) {
            throw $this->createNotFoundException('The content translation does not exist');
        }
        $locale = $request->getLocale();
        $events = $this->eventRepository->findByTranslation($translation);
        $eventsId = array_map(fn (LogEventInterface $e) => $e->getId()->toRfc4122(), $events);

        $form = $this->createForm(RevisionListType::class, null, ['event_ids' => $eventsId]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->handleFormNotSubmitted($events, $translation, $form, $locale);
        }

        $revisionsId = $form->getData();
        Assert::notNull($revisionsId);
        $versionId = $revisionsId['revision_version'];
        $diffId = $revisionsId['revision_diff'];

        $event = $this->getEventById($versionId);
        $previousEvent = $this->getEventById($diffId);

        if ($event === null || $previousEvent === null) {
            throw new \Exception('Revisions do not exist.');
        }

        return $this->renderRevisions($event, $previousEvent, $translation, $events, $form, $locale);
    }

    /**
     * @param array<LogEventInterface>                                                   $events
     * @param FormInterface<array{revision_version: string, revision_diff: string}|null> $form
     */
    private function handleFormNotSubmitted(
        array $events,
        ContentTranslationInterface $translation,
        FormInterface $form,
        string $locale
    ): Response {
        if (count($events) < 2) {
            return $this->render(
                '@XutimCore/admin/revision/translation_revisions.html.twig',
                [
                    'translation' => $translation,
                    'form' => $form->createView(),
                ]
            );
        }

        $previousEvent = array_slice($events, -2, 1)[0];
        $event = end($events);

        return $this->renderRevisions($event, $previousEvent, $translation, $events, $form, $locale);
    }

    private function getEventById(string $eventId): ?LogEventInterface
    {
        return $this->eventRepository->findOneBy(['id' => $eventId]);
    }


    /**
     * @param array<LogEventInterface>                                                   $events
     * @param FormInterface<array{revision_version: string, revision_diff: string}|null> $form
     */
    private function renderRevisions(
        LogEventInterface $event,
        LogEventInterface $previousEvent,
        ContentTranslationInterface $translation,
        array $events,
        FormInterface $form,
        string $locale
    ): Response {
        /** @var ContentTranslationCreatedEvent|ContentTranslationUpdatedEvent $domainEvent */
        $domainEvent = $event->getEvent();
        /** @var ContentTranslationCreatedEvent|ContentTranslationUpdatedEvent $previousDomainEvent */
        $previousDomainEvent = $previousEvent->getEvent();

        $titleDiff = $this->textDiff->generateHTMLDiff(
            $domainEvent->title,
            $previousDomainEvent->title
        );
        $bodyDiff = $this->textDiff->generateHTMLDiff(
            $this->fragmentsConverter->convertToAdminHtml($domainEvent->content, $locale),
            $this->fragmentsConverter->convertToAdminHtml($previousDomainEvent->content, $locale)
        );
        $descriptionDiff = $this->textDiff->generateHTMLDiff(
            $domainEvent->description,
            $previousDomainEvent->description
        );
        $usernames = $this->userRepository->findAllUsernamesByEmail();

        return $this->render('@XutimCore/admin/revision/translation_revisions.html.twig', [
            'translation' => $translation,
            'titleDiff' => $titleDiff,
            'descriptionDiff' => $descriptionDiff,
            'contentDiff' => $bodyDiff,
            'events' => $events,
            'form' => $form->createView(),
            'usernames' => $usernames,
            'isSubmitted' => $form->isSubmitted(),
        ]);
    }
}
