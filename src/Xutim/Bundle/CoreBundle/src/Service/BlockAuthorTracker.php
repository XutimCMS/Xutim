<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationCreatedEvent;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationUpdatedEvent;
use Xutim\CoreBundle\Domain\Model\LogEventInterface;

final class BlockAuthorTracker
{
    /**
     * Track which users modified which blocks across revisions between two selected points.
     *
     * @param array<LogEventInterface> $allRevisions All revisions (oldest first)
     * @param Uuid                     $oldId        Old revision ID
     * @param Uuid                     $newId        New revision ID
     *
     * @return array<string, list<string>> Block ID => list of user emails who modified it
     */
    public function getBlockAuthors(array $allRevisions, Uuid $oldId, Uuid $newId): array
    {
        $revisions = array_values($allRevisions);
        $oldIndex = null;
        $newIndex = null;

        foreach ($revisions as $index => $revision) {
            if ($revision->getId()->equals($oldId)) {
                $oldIndex = $index;
            }
            if ($revision->getId()->equals($newId)) {
                $newIndex = $index;
            }
        }

        if ($oldIndex === null || $newIndex === null) {
            return [];
        }

        if ($oldIndex > $newIndex) {
            [$oldIndex, $newIndex] = [$newIndex, $oldIndex];
        }

        /** @var array<string, list<string>> $blockAuthors */
        $blockAuthors = [];
        $previousBlocks = $this->getBlocksFromRevision($revisions[$oldIndex]);

        for ($i = $oldIndex + 1; $i <= $newIndex; $i++) {
            $revision = $revisions[$i];
            $currentBlocks = $this->getBlocksFromRevision($revision);
            $userEmail = $revision->getUserIdentifier();

            $changedBlockIds = $this->findChangedBlockIds($previousBlocks, $currentBlocks);

            foreach ($changedBlockIds as $blockId) {
                if (!isset($blockAuthors[$blockId])) {
                    $blockAuthors[$blockId] = [];
                }
                if (!in_array($userEmail, $blockAuthors[$blockId], true)) {
                    $blockAuthors[$blockId][] = $userEmail;
                }
            }

            $previousBlocks = $currentBlocks;
        }

        return $blockAuthors;
    }

    /**
     * @return array<string, array{type: string, data: string}>
     */
    private function getBlocksFromRevision(LogEventInterface $revision): array
    {
        $event = $revision->getEvent();

        if (!$event instanceof ContentTranslationCreatedEvent && !$event instanceof ContentTranslationUpdatedEvent) {
            return [];
        }

        /** @var EditorBlock $content */
        $content = $event->content;
        if (!isset($content['blocks'])) {
            return [];
        }

        $blocks = [];
        /** @var EditorBlocksUnion $block */
        foreach ($content['blocks'] as $block) {
            $dataJson = json_encode($block['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $blocks[$block['id']] = [
                'type' => $block['type'],
                'data' => $dataJson !== false ? $dataJson : '',
            ];
        }

        return $blocks;
    }

    /**
     * @param array<string, array{type: string, data: string}> $old
     * @param array<string, array{type: string, data: string}> $new
     *
     * @return list<string>
     */
    private function findChangedBlockIds(array $old, array $new): array
    {
        $changedIds = [];

        foreach ($new as $id => $newBlock) {
            if (!isset($old[$id])) {
                $changedIds[] = $id;
                continue;
            }

            $oldBlock = $old[$id];
            if ($oldBlock['type'] !== $newBlock['type']) {
                $changedIds[] = $id;
                continue;
            }

            if ($oldBlock['data'] !== $newBlock['data']) {
                $changedIds[] = $id;
            }
        }

        foreach (array_keys($old) as $id) {
            if (!isset($new[$id]) && !in_array($id, $changedIds, true)) {
                $changedIds[] = $id;
            }
        }

        return $changedIds;
    }
}
