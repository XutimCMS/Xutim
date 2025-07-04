<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Dto\Admin\ContentTranslation\ContentTranslationDto;
use Xutim\CoreBundle\Form\Admin\ContentTranslationType;
use Xutim\CoreBundle\Message\Command\ContentTranslation\CreateContentTranslationCommand;
use Xutim\CoreBundle\Message\Command\ContentTranslation\EditContentTranslationCommand;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\SecurityBundle\Security\UserInterface;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SecurityBundle\Service\UserStorage;

#[Route('/page/edit/{id}/{locale? }', name: 'admin_page_edit', methods: ['get', 'post'])]
class EditPageAction extends AbstractController
{
    public function __construct(
        private readonly SiteContext $siteContext,
        private readonly ContentTranslationRepository $transRepo,
        private readonly PageRepository $pageRepo,
        private readonly UserStorage $userStorage,
        private readonly MessageBusInterface $commandBus,
        private readonly ContentContext $contentContext,
        private readonly TranslatorAuthChecker $transAuthChecker,
        private readonly LogEventRepository $eventRepo
    ) {
    }

    public function __invoke(Request $request, string $id, string $locale = ''): Response
    {
        $page = $this->pageRepo->find($id);
        if ($page === null) {
            throw $this->createNotFoundException('The page does not exist');
        }
        $contentLocale = $this->contentContext->getLanguage();
        $translation = $page->getTranslationByLocale($contentLocale);

        $form = $this->createTranslationForm($page, $translation, $contentLocale, $locale);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ContentTranslationDto $data */
            $data = $form->getData();

            $this->addFlash('success', 'Changes were made successfully.');
            $command = $this->createTranslationCommand($translation, $data, $page);
            $this->commandBus->dispatch($command);

            return $this->redirectToRoute('admin_page_edit', ['id' => $page->getId()]);
        }

        if ($this->isGranted('ROLE_ADMIN') === false && $this->isGranted('ROLE_TRANSLATOR')) {
            /** @var UserInterface $user */
            $user = $this->getUser();
            $locales = $user->getTranslationLocales();
            $totalTranslations = count($locales);
        } else {
            $locales = null;
            $totalTranslations = count($this->siteContext->getLocales());
        }
        $translatedPages = $this->pageRepo->countTranslatedTranslations($page, $locales);


        if ($translation === null) {
            $revisionsCount = 0;
            $lastRevision = null;
        } else {
            $revisionsCount = $this->eventRepo->eventsCountPerTranslation($translation);
            $lastRevision = $this->eventRepo->findLastByTranslation($translation);
        }

        return $this->render('@XutimCore/admin/page/page_edit.html.twig', [
            'form' => $form,
            'page' => $page,
            'revisionsCount' => $revisionsCount,
            'lastRevision' => $lastRevision,
            'translation' => $translation,
            'totalTranslations' => $totalTranslations,
            'translatedTranslations' => $translatedPages
        ]);
    }

    /**
     * @return FormInterface<ContentTranslationDto>
     */
    private function createTranslationForm(PageInterface $page, ?ContentTranslationInterface $translation, string $contentLocale, string $locale): FormInterface
    {
        $existingTranslation = $translation;
        if (strlen(trim($locale)) > 0) {
            $this->transAuthChecker->denyUnlessCanTranslate($contentLocale);
            $translation = $this->transRepo->findOneBy([
                'page' => $page,
                'locale' => $locale
            ]);
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
            $this->transAuthChecker->denyUnlessCanTranslate($contentLocale);
            $data = new ContentTranslationDto('', '', '', '', [], '', $contentLocale);
        } else {
            $this->transAuthChecker->denyUnlessCanTranslate($translation->getLocale());
            $data = ContentTranslationDto::fromTranslation($translation);
        }

        return $this->createForm(ContentTranslationType::class, $data, [
            'existing_translation' => $existingTranslation
        ]);
    }

    private function createTranslationCommand(?ContentTranslationInterface $translation, ContentTranslationDto $data, PageInterface $page): CreateContentTranslationCommand|EditContentTranslationCommand
    {
        if ($translation === null) {
            return CreateContentTranslationCommand::fromDto(
                $data,
                $page->getId(),
                null,
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
