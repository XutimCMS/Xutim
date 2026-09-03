<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\BlockItemProvider;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Xutim\CoreBundle\Config\Layout\Block\Option\MediaFolderBlockItemOption;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;
use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;

final readonly class MediaFolderBlockItemProvider implements BlockItemProviderInterface
{
    public function __construct(
        private string $mediaFolderClass,
        private MediaFolderRepositoryInterface $mediaFolderRepository,
    ) {
    }

    public function getOptionClass(): string
    {
        return MediaFolderBlockItemOption::class;
    }

    /** @param FormBuilderInterface<mixed> $builder */
    public function buildFormFields(FormBuilderInterface $builder): void
    {
        $folders = $this->mediaFolderRepository->findAll();
        usort($folders, fn (MediaFolderInterface $a, MediaFolderInterface $b) => strcasecmp($a->fullName(), $b->fullName()));

        $builder->add('mediaFolder', EntityType::class, [
            'class' => $this->mediaFolderClass,
            'choices' => $folders,
            'label' => 'Media folder',
            'choice_label' => fn (MediaFolderInterface $folder) => $folder->fullName(),
            'placeholder' => new TranslatableMessage('select media folder', [], 'admin'),
            'required' => false,
        ]);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapDataToForms(BlockItemDto $dto, array $forms): void
    {
        if (!array_key_exists('mediaFolder', $forms)) {
            return;
        }
        $forms['mediaFolder']->setData($dto->mediaFolder);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapFormsToData(array $forms, BlockItemDto $dto): void
    {
        if (!array_key_exists('mediaFolder', $forms)) {
            return;
        }
        /** @var MediaFolderInterface|null $mediaFolder */
        $mediaFolder = $forms['mediaFolder']->getData();
        $dto->mediaFolder = $mediaFolder;
    }
}
