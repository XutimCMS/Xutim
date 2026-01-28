<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\ContentTranslation;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Xutim\CoreBundle\Repository\ContentDraftRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\SecurityBundle\Domain\Model\UserInterface;
use Xutim\SecurityBundle\Service\UserStorage;

class HeartbeatAction extends AbstractController
{
    public function __construct(
        private readonly ContentTranslationRepository $translationRepo,
        private readonly ContentDraftRepository $draftRepo,
        private readonly UserStorage $userStorage,
        private readonly ?HubInterface $hub,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $translation = $this->translationRepo->find($id);
        if ($translation === null) {
            throw $this->createNotFoundException('Content translation not found');
        }

        $user = $this->userStorage->getUserWithException();

        $currentEditor = $translation->getEditingUser();
        $isOtherEditing = $translation->isBeingEditedBy($user);

        if ($currentEditor === null || !$isOtherEditing) {
            // No one editing, or previous editor's heartbeat is stale — claim it
            $translation->startEditing($user);
        } elseif ($currentEditor->getId()->equals($user->getId())) {
            // Current user is already the editor — just refresh heartbeat
            $translation->heartbeat();
        }

        // Otherwise another user is actively editing — don't take over
        $otherEditingUser = $isOtherEditing ? $currentEditor : null;

        $this->translationRepo->save($translation, true);

        $this->publishPresenceEvent($translation->getId()->toRfc4122(), 'heartbeat', $user);

        $draft = $this->draftRepo->findDraft($translation);
        $draftUser = $draft?->getUser();

        return $this->json([
            'editingUser' => $otherEditingUser !== null ? [
                'id' => $otherEditingUser->getId()->toRfc4122(),
                'name' => $otherEditingUser->getName(),
            ] : null,
            'heartbeatAt' => $translation->getEditingHeartbeatAt()?->getTimestamp(),
            'isOtherUserEditing' => $isOtherEditing,
            'translationUpdatedAt' => $translation->getUpdatedAt()->getTimestamp(),
            'draftUpdatedAt' => $draft?->getUpdatedAt()?->getTimestamp(),
            'draftUpdatedBy' => ($draftUser !== null && !$draftUser->getId()->equals($user->getId()))
                ? $draftUser->getName()
                : null,
        ]);
    }

    private function publishPresenceEvent(string $translationId, string $type, UserInterface $user): void
    {
        if ($this->hub === null) {
            return;
        }

        try {
            $this->hub->publish(new Update(
                'content-translation/' . $translationId . '/presence',
                json_encode([
                    'type' => $type,
                    'userId' => $user->getId()->toRfc4122(),
                    'userName' => $user->getName(),
                    'timestamp' => time(),
                ], JSON_THROW_ON_ERROR),
            ));
        } catch (\Throwable $e) {
            $this->logger->warning('Failed to publish Mercure presence event: {message}', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
