<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Domain\Factory;

use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\EditorBundle\Domain\Model\ContentDraftInterface;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

class ContentDraftFactory
{
    /**
     * @param class-string<ContentDraftInterface> $entityClass
     */
    public function __construct(
        private readonly string $entityClass,
    ) {
    }

    public function create(
        ContentTranslationInterface $translation,
        ?UserInterface $user = null,
        ?ContentDraftInterface $basedOnDraft = null,
    ): ContentDraftInterface {
        return new ($this->entityClass)($translation, $user, $basedOnDraft);
    }

    public function createLiveVersion(ContentTranslationInterface $translation): ContentDraftInterface
    {
        return $this->create($translation, null, null);
    }

    public function createUserDraft(
        ContentTranslationInterface $translation,
        UserInterface $user,
        ContentDraftInterface $basedOnDraft,
    ): ContentDraftInterface {
        return $this->create($translation, $user, $basedOnDraft);
    }
}
