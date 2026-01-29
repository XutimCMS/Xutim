<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

final class RevisionChangeSummaryCalculator
{
    /**
     * Calculate a summary of changes between two revisions.
     *
     * @param list<array{op: string}> $contentRows
     *
     * @return array{
     *     titleChanged: bool,
     *     preTitleChanged: bool,
     *     subTitleChanged: bool,
     *     descriptionChanged: bool,
     *     blocksAdded: int,
     *     blocksRemoved: int,
     *     blocksModified: int
     * }
     */
    public function calculate(
        ?string $titleDiff,
        ?string $preTitleDiff,
        ?string $subTitleDiff,
        ?string $descriptionDiff,
        array $contentRows,
    ): array {
        return [
            'titleChanged' => $this->hasChange($titleDiff),
            'preTitleChanged' => $this->hasChange($preTitleDiff),
            'subTitleChanged' => $this->hasChange($subTitleDiff),
            'descriptionChanged' => $this->hasChange($descriptionDiff),
            'blocksAdded' => count(array_filter($contentRows, static fn (array $r): bool => $r['op'] === 'added')),
            'blocksRemoved' => count(array_filter($contentRows, static fn (array $r): bool => $r['op'] === 'removed')),
            'blocksModified' => count(array_filter(
                $contentRows,
                static fn (array $r): bool => in_array($r['op'], ['modified', 'modified_text'], true),
            )),
        ];
    }

    private function hasChange(?string $diff): bool
    {
        if ($diff === null || $diff === '') {
            return false;
        }

        return stripos($diff, '<ins') !== false || stripos($diff, '<del') !== false;
    }
}
