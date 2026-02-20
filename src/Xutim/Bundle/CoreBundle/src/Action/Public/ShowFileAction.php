<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;

class ShowFileAction extends AbstractController
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepo,
        private readonly StorageAdapterInterface $storage,
    ) {
    }

    public function __invoke(string $id): Response
    {
        $media = $this->mediaRepo->findById(Uuid::fromString($id));
        if ($media === null) {
            throw $this->createNotFoundException('The file does not exist');
        }

        $path = $this->storage->absolutePath($media->originalPath());

        $response = $this->file($path, $media->originalPath());

        $response->setPublic();
        $response->setMaxAge(604800); // 1 week
        $response->setSharedMaxAge(604800);
        $response->setExpires((new \DateTime())->modify('+1 week'));
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }
}
