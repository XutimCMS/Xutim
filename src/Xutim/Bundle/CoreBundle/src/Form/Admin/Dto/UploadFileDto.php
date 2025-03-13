<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\Dto;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Xutim\CoreBundle\Entity\Block;

final readonly class UploadFileDto
{
    /**
     * @param Collection<int, Block> $blocks
     */
    public function __construct(public UploadedFile $file, public Collection $blocks)
    {
    }
}
