<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\BlockItemProvider;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Xutim\CoreBundle\Config\Layout\Block\Option\ImageBlockItemOption;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;
use Xutim\MediaBundle\Domain\Model\MediaInterface;

final readonly class ImageBlockItemProvider implements BlockItemProviderInterface
{
    public function __construct(private string $mediaClass)
    {
    }

    public function getOptionClass(): string
    {
        return ImageBlockItemOption::class;
    }

    /** @param FormBuilderInterface<mixed> $builder */
    public function buildFormFields(FormBuilderInterface $builder): void
    {
        if ($builder->has('file')) {
            return;
        }

        $builder
            ->add('file', EntityType::class, [
                'class' => $this->mediaClass,
                'choice_label' => 'id',
                'placeholder' => new TranslatableMessage('select file', [], 'admin'),
                'required' => false,
                'attr' => ['data-controller' => 'media-field'],
                'row_attr' => ['class' => 'd-none'],
            ])
            ->add('fileDescription', TextType::class, [
                'label' => 'File description',
                'required' => false,
            ]);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapDataToForms(BlockItemDto $dto, array $forms): void
    {
        if (!array_key_exists('file', $forms)) {
            return;
        }
        $forms['file']->setData($dto->file);
        $forms['fileDescription']->setData($dto->fileDescription);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapFormsToData(array $forms, BlockItemDto $dto): void
    {
        if (!array_key_exists('file', $forms)) {
            return;
        }
        /** @var MediaInterface|null $file */
        $file = $forms['file']->getData();
        $dto->file = $file;
        /** @var string|null $description */
        $description = $forms['fileDescription']->getData();
        $dto->fileDescription = $description;
    }
}
