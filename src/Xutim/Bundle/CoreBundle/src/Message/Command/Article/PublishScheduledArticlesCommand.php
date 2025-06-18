<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\Article;

final readonly class PublishScheduledArticlesCommand
{
    public function __construct(
        public string $userIdentifier
    ) {
    }
}
