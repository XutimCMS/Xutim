<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Repository;

use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Domain\Model\MediaTranslationInterface;

interface MediaTranslationRepositoryInterface
{
    public function save(MediaTranslationInterface $translation, bool $flush = false): void;

    public function removeByMedia(MediaInterface $media): void;
}
