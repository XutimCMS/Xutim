<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\Article;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Form\Admin\Dto\CreateArticleFormData;

final readonly class CreateArticleCommand
{
    /**
     * @param EditorBlock $content
     */
    public function __construct(
        public ?string $layout,
        public string $preTitle,
        public string $title,
        public string $subTitle,
        public string $slug,
        public array $content,
        public string $description,
        public string $defaultLanguage,
        public string $userIdentifier,
        public ?Uuid $featuredImageId
    ) {
    }

    public static function fromFormData(CreateArticleFormData $data, string $userIdentifier): self
    {
        return new self(
            $data->getLayout(),
            $data->getPreTitle(),
            $data->getTitle(),
            $data->getSubTitle(),
            $data->getSlug(),
            $data->getContent(),
            $data->getDescription(),
            $data->getLocale(),
            $userIdentifier,
            $data->getFeaturedImageId()
        );
    }

    /**
     * @phpstan-assert-if-true Uuid $this->featuredImageId
     * @phpstan-assert-if-false null $this->featuredImageId
     */
    public function hasFeaturedImage(): bool
    {
        return $this->featuredImageId !== null;
    }
}
