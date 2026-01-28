<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\ContentDraft;

use Symfony\Component\Uid\Uuid;

final readonly class DiscardContentDraftCommand
{
    public function __construct(
        public Uuid $draftId,
        public string $userIdentifier,
    ) {
    }
}
