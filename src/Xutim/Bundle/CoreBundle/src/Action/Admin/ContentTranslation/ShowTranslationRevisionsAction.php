<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\ContentTranslation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationCreatedEvent;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationUpdatedEvent;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Form\Admin\RevisionListType;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Repository\UserRepository;
use Xutim\CoreBundle\Service\ContentFragmentsConverter;
use Xutim\CoreBundle\Service\TextDiff;

#[Route('/content-translation/revisions/{id}/{version?}/{diff?}', name: 'admin_content_translation_revisions', methods: ['GET'])]
class ShowTranslationRevisionsAction extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly TextDiff $textDiff,
        private readonly ContentFragmentsConverter $fragmentsConverter,
        private readonly UserRepository $userRepository
    ) {
    }

    public function __invoke(
        ContentTranslation $translation,
        Request $request
    ): Response {
        $events = $this->eventRepository->findByTranslation($translation);
        $eventsId = array_map(fn (Event $e) => $e->getId()->toRfc4122(), $events);

        $form = $this->createForm(RevisionListType::class, null, ['event_ids' => $eventsId]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->handleFormNotSubmitted($events, $translation, $form);
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

        return $this->renderRevisions($event, $previousEvent, $translation, $events, $form);
    }

    /**
     * @param array<Event>                    $events
     * @param FormInterface<array{revision_version: string, revision_diff: string}|null> $form
     */
    private function handleFormNotSubmitted(
        array $events,
        ContentTranslation $translation,
        FormInterface $form
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

        return $this->renderRevisions($event, $previousEvent, $translation, $events, $form);
    }

    private function getEventById(string $eventId): ?Event
    {
        return $this->eventRepository->findOneBy(['id' => $eventId]);
    }


    /**
     * @param array<Event>                    $events
     * @param FormInterface<array{revision_version: string, revision_diff: string}|null> $form
     */
    private function renderRevisions(
        Event $event,
        Event $previousEvent,
        ContentTranslation $translation,
        array $events,
        FormInterface $form
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
            $this->fragmentsConverter->convertToAdminHtml($domainEvent->content),
            $this->fragmentsConverter->convertToAdminHtml($previousDomainEvent->content)
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
