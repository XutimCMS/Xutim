<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Tag;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Entity\TagTranslation;
use Xutim\CoreBundle\Form\Admin\Dto\TagDto;
use Xutim\CoreBundle\Form\Admin\TagType;
use Xutim\CoreBundle\Message\Command\Tag\EditTagCommand;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Domain\Model\UserInterface;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SecurityBundle\Service\UserStorage;

class EditTagAction extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly ContentContext $context,
        private readonly TranslatorAuthChecker $transAuthChecker,
        private readonly UserStorage $userStorage,
        private readonly LogEventRepository $eventRepo,
        private readonly TagRepository $tagRepo,
        private readonly SiteContext $siteContext,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $tag = $this->tagRepo->find($id);
        if ($tag === null) {
            throw $this->createNotFoundException('The tag does not exist');
        }
        $locale = $this->context->getLanguage();
        /** @var null|TagTranslation $translation */
        $translation = $tag->getTranslationByLocale($locale);
        $form = $this->createForm(TagType::class, TagDto::fromTag($tag, $locale), [
            'disabled' => $this->transAuthChecker->canTranslate($locale) === false,
            'existing_translation' => $translation
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->transAuthChecker->denyUnlessCanTranslate($locale);
            /** @var TagDto $data */
            $data = $form->getData();

            $this->commandBus->dispatch(new EditTagCommand(
                $tag->getId(),
                $data->name,
                $data->slug,
                $locale,
                $data->color,
                $data->featuredImageId,
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                $data->layout
            ));

            $this->addFlash('success', 'Changes were made successfully.');

            return new RedirectResponse($this->router->generate('admin_tag_edit', ['id' => $tag->getId()]));
        }

        if ($this->isGranted(UserRoles::ROLE_ADMIN) === false && $this->isGranted(UserRoles::ROLE_TRANSLATOR)) {
            /** @var UserInterface $user */
            $user = $this->getUser();
            $locales = $user->getTranslationLocales();
            $totalTranslations = count($locales);
        } else {
            $locales = null;
            $totalTranslations = count($this->siteContext->getLocales());
        }
        $translatedTags = $this->tagRepo->countTranslatedTranslations($tag, $locales);

        $revisionsCount = $translation === null ? 0 : $this->eventRepo->eventsCountPerTranslation($translation);
        $lastRevision = $translation === null ? null : $this->eventRepo->findLastByTranslation($translation);

        return $this->render('@XutimCore/admin/tag/tag_edit.html.twig', [
            'tag' => $tag,
            'translation' => $translation,
            'form' => $form,
            'lastRevision' => $lastRevision,
            'totalTranslations' => $totalTranslations,
            'translatedTranslations' => $translatedTags
        ]);
    }
}
