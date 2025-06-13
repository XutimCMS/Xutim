<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Components\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Form\Admin\FileOrMediaType;

#[AsLiveComponent]
class FileUpload extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    /**
     * @return FormInterface<array{new_file: ?UploadedFile, existing_file: ?File, name: ?string, alt: ?string, locale: ?string, copyright: ?string}|null>
    */
    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(FileOrMediaType::class);
    }
}
