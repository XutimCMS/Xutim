<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Action\Admin;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\UserInterface;
use Xutim\CoreBundle\Service\FlashNotifier;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;
use Xutim\RedirectBundle\Form\RedirectFormData;
use Xutim\RedirectBundle\Form\RedirectType;
use Xutim\RedirectBundle\Infra\Routing\RedirectRouteService;

class EditRedirectAction
{
    public function __construct(
        private readonly RedirectRepositoryInterface $repo,
        private readonly SiteContext $siteContext,
        private readonly Environment $twig,
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $router,
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly FlashNotifier $flashNotifier,
        private readonly string $contentTranslationClass,
        private readonly RedirectRouteService $redirectRouteService
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $redirect = $this->repo->findById($id);
        if ($redirect === null) {
            throw new NotFoundHttpException('The redirect does not exist');
        }
        if ($this->authChecker->isGranted(UserInterface::ROLE_EDITOR) === false) {
            throw new AccessDeniedException('Access denied.');
        }
        $locales = $this->siteContext->getLocales();
        $localeChoices = array_combine($locales, $locales);
        $form = $this->formFactory->create(RedirectType::class, RedirectFormData::fromRedirect($redirect), [
            'action' => $this->router->generate('admin_redirect_edit', ['id' => $redirect->getId()]),
            'locale_choices' => $localeChoices,
            'content_translation_class' => $this->contentTranslationClass
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var RedirectFormData $data */
            $data = $form->getData();

            $redirect->change(
                $data->getSource(),
                $data->getTargetContentTranslation(),
                $data->getLocale(),
                $data->isPermanent()
            );
            $this->repo->save($redirect, true);
            $this->redirectRouteService->resetRedirectRoutesCache();

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->twig
                    ->load('@XutimRedirect/admin/redirect/redirect_edit.html.twig')
                    ->renderBlock('stream_success', ['redirect' => $redirect]);

                $this->flashNotifier->stream($stream);
            }

            $this->flashNotifier->changesSaved();

            return new RedirectResponse(
                $this->router->generate('admin_redirect_edit', ['id' => $redirect->getId()])
            );
        }

        $html = $this->twig->render('@XutimRedirect/admin/redirect/redirect_edit.html.twig', [
            'form' => $form->createView(),
            'redirect' => $redirect
        ]);

        return new Response($html);
    }
}
