<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\Page;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Dto\Admin\Page\PageTranslationLocalesDto;

final readonly class EditPageTranslationLocalesCommand
{
    /** @param list<string> $translationLocales */
    public function __construct(
        public Uuid $pageId,
        public bool $allTranslationLocales,
        public array $translationLocales,
        public string $userIdentifier,
    ) {
    }

    public static function fromDto(PageTranslationLocalesDto $dto, Uuid $pageId, string $userIdentifier): self
    {
        return new self($pageId, $dto->allTranslationLocales, $dto->translationLocales, $userIdentifier);
    }
}
