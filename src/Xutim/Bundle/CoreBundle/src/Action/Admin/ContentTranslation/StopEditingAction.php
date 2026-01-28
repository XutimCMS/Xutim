<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\ContentTranslation;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\SecurityBundle\Domain\Model\UserInterface;
use Xutim\SecurityBundle\Service\UserStorage;

class StopEditingAction extends AbstractController
{
    public function __construct(
        private readonly ContentTranslationRepository $translationRepo,
        private readonly UserStorage $userStorage,
        private readonly ?HubInterface $hub,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(string $id): Response
    {
        $translation = $this->translationRepo->find($id);
        if ($translation === null) {
            throw $this->createNotFoundException('Content translation not found');
        }

        $user = $this->userStorage->getUserWithException();

        $editingUser = $translation->getEditingUser();
        if ($editingUser !== null && $editingUser->getId()->equals($user->getId())) {
            $translation->stopEditing();
            $this->translationRepo->save($translation, true);

            $this->publishStopEvent($translation->getId()->toRfc4122(), $user);
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    private function publishStopEvent(string $translationId, UserInterface $user): void
    {
        if ($this->hub === null) {
            return;
        }

        try {
            $this->hub->publish(new Update(
                'content-translation/' . $translationId . '/presence',
                json_encode([
                    'type' => 'stopped',
                    'userId' => $user->getId()->toRfc4122(),
                    'timestamp' => time(),
                ], JSON_THROW_ON_ERROR),
            ));
        } catch (\Throwable $e) {
            $this->logger->warning('Failed to publish Mercure stop event: {message}', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
