<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\Page;

final readonly class UploadFileMessage
{
    public Uuid $id;

    public function __construct(
        public UploadedFile $file,
        public string $userIdentifier,
        public ?Page $page = null,
        public ?Article $article = null,
        public string $name = '',
        public string $alt = '',
        public string $locale = 'en',
    ) {
        $this->id = Uuid::v4();
    }
}
