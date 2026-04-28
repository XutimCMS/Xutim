<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\PublicationStatus;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\UX\Turbo\TurboBundle;
use Xutim\CoreBundle\Entity\PublicationStatus;
use Xutim\CoreBundle\Message\Command\PublicationStatus\ChangePublicationStatusCommand;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SecurityBundle\Service\UserStorage;

class ChangeStatusAction extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserStorage $userStorage,
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly TranslatorAuthChecker $transAuthChecker,
        private readonly ContentTranslationRepository $transRepo
    ) {
    }

    public function __invoke(
        Request $request,
        string $id,
        PublicationStatus $status
    ): Response {
        $translation = $this->transRepo->find($id);
        if ($translation === null) {
            throw $this->createNotFoundException('The content translation does not exist');
        }
        $this->transAuthChecker->denyUnlessCanTranslate($translation->getLocale());
        $this->csrfTokenChecker->checkTokenFromFormRequest('xutim-dialog', $request);

        $user = $this->userStorage->getUserWithException();
        $applyToAll = $request->request->getBoolean('apply_to_all');

        $targets = [$translation];
        if ($applyToAll === true) {
            $targets = $translation->getObject()->getTranslations()->toArray();
            foreach ($targets as $target) {
                $this->transAuthChecker->denyUnlessCanTranslate($target->getLocale());
            }
        }

        foreach ($targets as $target) {
            $this->commandBus->dispatch(new ChangePublicationStatusCommand(
                $target->getId(),
                $status,
                $user->getUserIdentifier()
            ));
        }
        $this->addFlash('success', 'flash.changes_made_successfully');

        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            $stream = $this->renderBlockView('@XutimCore/admin/translation/_status_item.html.twig', 'stream_success', [
                'translation' => $translation
            ]);
            $this->addFlash('stream', $stream);

            return $this->redirect($request->headers->get('referer', '/'));
        }

        return $this->redirect($request->headers->get('referer', '/'));
    }
}
