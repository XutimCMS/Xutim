<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;

final readonly class UploadFileMessage
{
    public Uuid $id;

    public function __construct(
        public UploadedFile $file,
        public string $userIdentifier,
        public ?PageInterface $page = null,
        public ?ArticleInterface $article = null,
        public string $name = '',
        public string $alt = '',
        public string $locale = 'en',
        public string $copyright = ''
    ) {
        $this->id = Uuid::v4();
    }
}
