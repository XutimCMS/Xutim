<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Xutim\CoreBundle\Domain\Model\ContentDraftInterface as BaseDraftInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Entity\ContentDraft as BaseContentDraft;
use Xutim\EditorBundle\Domain\Model\ContentBlockInterface;
use Xutim\EditorBundle\Domain\Model\ContentDraftInterface;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

#[MappedSuperclass]
class ContentDraft extends BaseContentDraft implements ContentDraftInterface
{
    /** @var Collection<int, ContentBlockInterface> */
    #[OneToMany(mappedBy: 'draft', targetEntity: ContentBlockInterface::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[OrderBy(['position' => 'ASC'])]
    private Collection $blocks;

    public function __construct(
        ContentTranslationInterface $translation,
        ?UserInterface $user = null,
        ?BaseDraftInterface $basedOnDraft = null,
    ) {
        parent::__construct($translation, $user, $basedOnDraft);
        $this->blocks = new ArrayCollection();
    }

    /**
     * @return Collection<int, ContentBlockInterface>
     */
    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    /**
     * @return Collection<int, ContentBlockInterface>
     */
    public function getTopLevelBlocks(): Collection
    {
        return $this->blocks->filter(
            fn (ContentBlockInterface $block) => $block->getParent() === null
        );
    }

    public function addBlock(ContentBlockInterface $block): void
    {
        if ($this->blocks->contains($block)) {
            return;
        }
        $this->blocks->add($block);
    }

    public function removeBlock(ContentBlockInterface $block): void
    {
        $this->blocks->removeElement($block);
    }
}
