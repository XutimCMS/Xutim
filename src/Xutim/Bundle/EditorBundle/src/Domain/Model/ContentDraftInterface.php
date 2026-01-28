<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Domain\Model;

use Doctrine\Common\Collections\Collection;
use Xutim\CoreBundle\Domain\Model\ContentDraftInterface as BaseContentDraftInterface;

interface ContentDraftInterface extends BaseContentDraftInterface
{
    /**
     * @return Collection<int, ContentBlockInterface>
     */
    public function getBlocks(): Collection;

    /**
     * @return Collection<int, ContentBlockInterface>
     */
    public function getTopLevelBlocks(): Collection;

    public function addBlock(ContentBlockInterface $block): void;

    public function removeBlock(ContentBlockInterface $block): void;
}
