<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Domain\Repository;

use Xutim\SnippetBundle\Domain\Model\SnippetTranslationInterface;

interface SnippetTranslationRepositoryInterface
{
    public function save(SnippetTranslationInterface $entity, bool $flush = false): void;

    public function remove(SnippetTranslationInterface $entity, bool $flush = false): void;
}
