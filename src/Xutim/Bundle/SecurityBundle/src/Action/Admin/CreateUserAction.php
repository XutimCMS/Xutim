<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Action\Admin;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;
use Xutim\CoreBundle\Service\FlashNotifier;
use Xutim\SecurityBundle\Domain\Model\UserInterface;
use Xutim\SecurityBundle\Form\CreateUserFormData;
use Xutim\SecurityBundle\Form\CreateUserType;
use Xutim\SecurityBundle\Message\CreateUserCommand;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

#[Route('/user/new', name: 'admin_user_new')]
class CreateUserAction
{
    /**
     * @param UserRepositoryInterface&ServiceEntityRepository<UserInterface> $userRepo
     */
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserRepositoryInterface $userRepo,
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly Environment $twig,
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $router,
        private readonly FlashNotifier $flashNotifier,
        private readonly UserStorage $userStorage,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if ($this->authChecker->isGranted(UserRoles::ROLE_ADMIN) === false) {
            throw new AccessDeniedException('Access denied.');
        }
        $form = $this->formFactory->create(CreateUserType::class, null, [
            'action' => $this->router->generate('admin_user_new')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CreateUserFormData $data */
            $data = $form->getData();
            $email = $data->email;
            $this->commandBus->dispatch(new CreateUserCommand(
                $data->email,
                $data->name,
                $data->password,
                $data->roles,
                $data->transLocales,
                $this->userStorage->getUserWithException()->getUserIdentifier()
            ));

            $this->flashNotifier->changesSaved();

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->twig
                    ->load('@XutimSecurity/user/create.html.twig')
                    ->renderBlock('stream_success', [
                        'user' => $this->userRepo->findOneByEmail($email)
                    ]);

                $this->flashNotifier->stream($stream);
            }

            return new RedirectResponse(
                $this->router->generate('admin_user_list', ['searchTerm' => ''])
            );
        }

        $html = $this->twig->render('@XutimSecurity/user/create.html.twig', [
            'form' => $form->createView(),
        ]);

        return new Response($html);
    }
}
