<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Action\Admin;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;
use Xutim\CoreBundle\Service\FlashNotifier;
use Xutim\RedirectBundle\Domain\Factory\RedirectFactoryInterface;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;
use Xutim\RedirectBundle\Form\RedirectFormData;
use Xutim\RedirectBundle\Form\RedirectType;
use Xutim\RedirectBundle\Infra\Routing\RedirectRouteService;
use Xutim\SecurityBundle\Security\UserRoles;

class CreateRedirectAction
{
    public function __construct(
        private readonly RedirectRepositoryInterface $repo,
        private readonly RedirectFactoryInterface $factory,
        private readonly Environment $twig,
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $router,
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly FlashNotifier $flashNotifier,
        private readonly RedirectRouteService $redirectRouteService
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if ($this->authChecker->isGranted(UserRoles::ROLE_EDITOR) === false) {
            throw new AccessDeniedException('Access denied.');
        }
        $form = $this->formFactory->create(RedirectType::class, null, [
            'action' => $this->router->generate('admin_redirect_new'),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var RedirectFormData $data */
            $data = $form->getData();
            $redirect = $this->factory->create(
                $data->getSource(),
                $data->getTarget(),
                $data->isPermanent(),
            );

            $this->repo->save($redirect, true);
            $this->redirectRouteService->resetRedirectRoutesCache();
            if ($request->headers->has('turbo-frame')) {
                $stream = $this->twig
                    ->load('@XutimRedirect/admin/redirect/redirect_new.html.twig')
                    ->renderBlock('stream_success', ['redirect' => $redirect]);

                $this->flashNotifier->stream($stream);
            }
            $this->flashNotifier->changesSaved();

            return new RedirectResponse(
                $this->router->generate('admin_redirect_list', ['searchTerm' => ''])
            );
        }

        $html = $this->twig->render('@XutimRedirect/admin/redirect/redirect_new.html.twig', [
            'form' => $form->createView()
        ]);

        return new Response($html);
    }
}
