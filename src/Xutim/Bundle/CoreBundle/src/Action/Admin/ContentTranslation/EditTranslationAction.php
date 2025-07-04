<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\ContentTranslation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Dto\Admin\ContentTranslation\ContentTranslationDto;
use Xutim\CoreBundle\Exception\LogicException;
use Xutim\CoreBundle\Form\Admin\ContentTranslationType;
use Xutim\CoreBundle\Message\Command\ContentTranslation\EditContentTranslationCommand;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SecurityBundle\Service\UserStorage;

#[Route('/content-translation/edit/{id}', name: 'admin_content_translation_edit')]
class EditTranslationAction extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserStorage $userStorage,
        private readonly ContentTranslationRepository $contentTransRepo,
        private readonly TranslatorAuthChecker $transAuthChecker,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $translation = $this->contentTransRepo->find($id);
        if ($translation === null) {
            throw $this->createNotFoundException('The content translation does not exist');
        }
        $missingTranslations = $this->contentTransRepo->findMissingTranslationLocales($translation->getObject());
        $missingTranslations[] = $translation->getLocale();
        $form = $this->createForm(
            ContentTranslationType::class,
            ContentTranslationDto::fromTranslation($translation),
            [
                'disabled' => $this->transAuthChecker->canTranslate($translation->getLocale()) === false,
                'existing_translation' => $translation
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->transAuthChecker->denyUnlessCanTranslate($translation->getLocale());
            /** @var ContentTranslationDto $translationDto */
            $translationDto = $form->getData();

            $this->commandBus->dispatch(
                EditContentTranslationCommand::fromDto(
                    $translationDto,
                    $translation->getId(),
                    $this->userStorage->getUserWithException()->getUserIdentifier()
                )
            );

            $this->addFlash('success', 'Translation updated!');

            return $this->redirectTranslationResponse($translation);
        }

        if ($translation->hasArticle()) {
            $article = $translation->getArticle();
            return $this->render('@XutimCore/admin/article/article_edit.html.twig', [
                'form' => $form,
                'article' => $article,
                'defaultTranslation' => $article->getDefaultTranslation(),
                'translation' => $translation
            ]);
        }

        if ($translation->hasPage()) {
            $page = $translation->getPage();
            return $this->render('@XutimCore/admin/page/page_edit.html.twig', [
                'form' => $form,
                'page' => $page,
                'defaultTranslation' => $page->getDefaultTranslation(),
                'translation' => $translation
            ]);
        }

        throw new LogicException('Content translation should have either article or page.');
    }

    private function redirectTranslationResponse(ContentTranslationInterface $translation): RedirectResponse
    {
        if ($translation->hasArticle()) {
            return $this->redirectToRoute('admin_article_show', ['id' => $translation->getArticle()->getId()]);
        }

        if ($translation->hasPage()) {
            return $this->redirectToRoute('admin_page_list', ['id' => $translation->getPage()->getId()]);
        }

        throw new LogicException('Content translation should have either article or page.');
    }
}
