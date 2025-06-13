<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Factory;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\FileTranslationInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;

class FileFactory
{
    public function __construct(
        private readonly string $fileClass,
        private readonly string $fileTranslationClass
    ) {
        if (!class_exists($fileClass)) {
            throw new \InvalidArgumentException(sprintf('File class "%s" does not exist.', $fileClass));
        }

        if (!class_exists($fileTranslationClass)) {
            throw new \InvalidArgumentException(sprintf('File translation class "%s" does not exist.', $fileTranslationClass));
        }
    }

    public function create(
        Uuid $id,
        string $name,
        string $alt,
        string $locale,
        string $dataPath,
        string $extension,
        string $reference,
        string $hash,
        string $copyright,
        ?ArticleInterface $article,
        ?PageInterface $page
    ): FileInterface {
        /** @var FileInterface $file */
        $file = new ($this->fileClass)(
            $id,
            $dataPath,
            $extension,
            $reference,
            $hash,
            $copyright,
            $article,
            $page
        );

        /** @var FileTranslationInterface $trans */
        $trans = new ($this->fileTranslationClass)(
            $locale,
            $name,
            $alt,
            $file
        );
        $file->addTranslation($trans);

        return $file;
    }
}
