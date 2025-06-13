<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\File;

use Xutim\CoreBundle\Message\Command\File\UploadFileMessage;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Service\FileService;

readonly class UploadFileHandler implements CommandHandlerInterface
{
    public function __construct(private FileService $fileService)
    {
    }

    public function __invoke(UploadFileMessage $cmd): void
    {
        $file = $this->fileService->persistFile($cmd);
    }
}
