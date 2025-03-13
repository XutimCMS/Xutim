<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\ContentTranslation;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Dto\Admin\ContentTranslation\ContentTranslationDto;

final readonly class EditContentTranslationCommand
{
    /**
     * @param array{}|array{
     *     time: int,
     *     blocks: array{}|array<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $content
     */
    public function __construct(
        public Uuid $translationId,
        public string $preTitle,
        public string $title,
        public string $subTitle,
        public string $slug,
        public array $content,
        public string $description,
        public string $locale,
        public string $userIdentifier
    ) {
    }

    public static function fromDto(
        ContentTranslationDto $dto,
        Uuid $translationId,
        string $userIdentifier
    ): self {
        return new self(
            $translationId,
            $dto->preTitle,
            $dto->title,
            $dto->subTitle,
            $dto->slug,
            $dto->content,
            $dto->description,
            $dto->locale,
            $userIdentifier
        );
    }
}
