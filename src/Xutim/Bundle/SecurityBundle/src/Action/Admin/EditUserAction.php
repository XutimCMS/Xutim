<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Action\Admin\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Service\FlashNotifier;
use Xutim\SecurityBundle\Form\EditUserFormData;
use Xutim\SecurityBundle\Form\EditUserType;
use Xutim\SecurityBundle\Message\EditUserCommand;
use Xutim\SecurityBundle\Repository\ResetPasswordRequestRepository;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;
use Xutim\SecurityBundle\Security\UserInterface;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

#[Route('/user/edit/{id}', name: 'admin_user_edit')]
class EditUserAction
{
    /**
     * @param UserRepositoryInterface&ServiceEntityRepository<UserInterface> $userRepo
     */
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserStorage $userStorage,
        private readonly LogEventRepository $eventRepository,
        private readonly ResetPasswordRequestRepository $resetPasswordRequestRepository,
        private readonly UserRepositoryInterface $userRepo,
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly Environment $twig,
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $router,
        private readonly FlashNotifier $flashNotifier,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if ($this->authChecker->isGranted(UserRoles::ROLE_ADMIN) === false) {
            throw new AccessDeniedException('Access denied.');
        }

        $user = $this->userRepo->findById($id);
        if ($user === null) {
            throw new NotFoundHttpException('The user does not exist');
        }
        $form = $this->formFactory->create(
            EditUserType::class,
            new EditUserFormData(
                $user->getName(),
                $user->getEmail(),
                $user->getRoles(),
                $user->getTranslationLocales()
            ),
            [
                'existing_user' => $user,
                'action' => $this->router->generate('admin_user_edit', ['id' => $user->getId()])
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var EditUserFormData $data */
            $data = $form->getData();
            $command = new EditUserCommand(
                $user->getId(),
                $data->name,
                $data->roles,
                $data->translationLocales,
                $this->userStorage->getUserWithException()->getUserIdentifier()
            );
            $this->commandBus->dispatch($command);

            $this->flashNotifier->changesSaved();

            if ($request->headers->has('turbo-frame')) {
                $token = $this->resetPasswordRequestRepository->findOneBy(['user' => $user]);
                $stream = $this->twig
                    ->load('@XutimSecurity/user/show.html.twig')
                    ->renderBlock('stream_success', [
                        'user' => $user,
                        'events' => $this->eventRepository->findBy(['objectId' => $user->getId()]),
                        'resetPasswordSent' => $token !== null && $token->isExpired() === false
                    ]);

                $this->flashNotifier->stream($stream);
            }

            return new RedirectResponse(
                $this->router->generate('admin_user_show', ['id' => $user->getId()])
            );
        }

        $html = $this->twig->render('@XutimSecurity/user/create.html.twig', [
            'form' => $form->createView()
        ]);

        return new Response($html);
    }
}
