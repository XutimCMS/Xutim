<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Action\Admin;

use App\Entity\Event\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Security\TranslatorAuthChecker;
use Xutim\EventBundle\Domain\Factory\EventTranslationFactory;
use Xutim\EventBundle\Domain\Model\EventTranslationInterface;
use Xutim\EventBundle\Form\Admin\EventTranslationDto;
use Xutim\EventBundle\Form\Admin\EventTranslationType;
use Xutim\EventBundle\Infra\Doctrine\ORM\EventRepository;
use Xutim\EventBundle\Infra\Doctrine\ORM\EventTranslationRepository;

#[Route('/event/edit/{id}/{locale? }', name: 'admin_event_edit', methods: ['get', 'post'])]
class EditEventAction extends AbstractController
{
    public function __construct(
        private readonly TranslatorAuthChecker $transAuthChecker,
        private readonly ContentContext $context,
        private readonly EventTranslationRepository $eventTransRepo,
        private readonly EventRepository $eventRepo,
        private readonly EventTranslationFactory $eventTranslationFactory,
    ) {
    }

    public function __invoke(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $locale = $this->context->getLanguage();
        /** @var null|EventTranslationInterface $translation */
        $translation = $event->getTranslationByLocale($locale);
        $form = $this->createForm(EventTranslationType::class, $translation === null ? null : EventTranslationDto::fromEventTranslation($translation, $locale), [
            'disabled' => $this->transAuthChecker->canTranslate($locale) === false
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->transAuthChecker->denyUnlessCanTranslate($locale);
            /** @var EventTranslationDto $data */
            $data = $form->getData();

            if ($translation === null) {
                $translation = $this->eventTranslationFactory->create($data->title, $data->location, $data->description, $locale, $event);
                $event->addTranslation($translation);
            } else {
                $translation->change($data->title, $data->location, $data->description);
            }
            $this->eventTransRepo->save($translation);
            $this->eventRepo->save($event, true);

            $this->addFlash('success', 'Changes were made successfully.');

            return $this->redirectToRoute('admin_event_edit', ['id' => $event->getId()]);
        }

        // if ($this->isGranted(User::ROLE_ADMIN) === false && $this->isGranted(User::ROLE_TRANSLATOR)) {
        //     /** @var User $user */
        //     $user = $this->getUser();
        //     $locales = $user->getTranslationLocales();
        //     $totalTranslations = count($locales);
        // } else {
        //     $locales = null;
        //     $totalTranslations = count($this->siteContext->getLocales());
        // }
        // $translatedTags = $this->tagRepo->countTranslatedTranslations($tag, $locales);
        //
        // $revisionsCount = $translation === null ? 0 : $this->eventRepo->eventsCountPerTranslation($translation);
        // $lastRevision = $translation === null ? null : $this->eventRepo->findLastByTranslation($translation);

        return $this->render('@XutimEvent/admin/event/event_edit.html.twig', [
            'event' => $event,
            'translation' => $translation,
            'form' => $form,
            // 'lastRevision' => $lastRevision,
            // 'totalTranslations' => $totalTranslations,
            // 'translatedTranslations' => $translatedTags
        ]);
    }
}
