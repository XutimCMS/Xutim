<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Application;

use App\Entity\Media\Media;
use App\Entity\Media\MediaFolder;
use App\Entity\Media\MediaTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Tests\Application\Admin\AdminApplicationTestCase;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Domain\Model\MediaTranslationInterface;

abstract class MediaApplicationTestCase extends AdminApplicationTestCase
{
    protected function createMediaInDb(
        string $originalPath = 'test/file.jpg',
        string $mime = 'image/jpeg',
        int $width = 800,
        int $height = 600,
        ?MediaFolderInterface $folder = null,
        string $innerName = 'Test Media',
    ): MediaInterface {
        $ext = pathinfo($originalPath, PATHINFO_EXTENSION);

        $media = new Media(
            folder: $folder,
            originalPath: $originalPath,
            originalExt: $ext,
            mime: $mime,
            hash: hash('sha256', $originalPath . uniqid()),
            sizeBytes: 1024,
            width: $width,
            height: $height,
            innerName: $innerName,
        );

        $em = $this->getEntityManager();
        $em->persist($media);
        $em->flush();

        return $media;
    }

    protected function createNonImageMediaInDb(
        ?MediaFolderInterface $folder = null,
    ): MediaInterface {
        return $this->createMediaInDb(
            originalPath: 'test/document-' . uniqid() . '.pdf',
            mime: 'application/pdf',
            width: 0,
            height: 0,
            folder: $folder,
        );
    }

    protected function createFolderInDb(string $name = 'Test Folder'): MediaFolderInterface
    {
        $folder = new MediaFolder(
            code: Uuid::v4()->toRfc4122(),
            name: $name,
            basePath: '',
        );

        $em = $this->getEntityManager();
        $em->persist($folder);
        $em->flush();

        return $folder;
    }

    protected function createTranslationInDb(
        MediaInterface $media,
        string $locale = 'en',
        string $name = 'Test Image',
        string $alt = 'Test alt text',
    ): MediaTranslationInterface {
        $translation = new MediaTranslation($media, $locale, $name, $alt);

        $em = $this->getEntityManager();
        $em->persist($translation);
        $em->flush();
        $media->addTranslation($translation);

        return $translation;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine.orm.entity_manager');
    }
}
