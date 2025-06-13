<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Uid\UuidV4;
use Traversable;
use Xutim\CoreBundle\Form\Admin\Dto\ImageDto;

/**
 * @template-extends AbstractType<ImageDto>
 * @template-implements DataMapperInterface<ImageDto>
 */
class FeaturedImageType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('featuredImage', HiddenType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin')
            ])
            ->setDataMapper($this);
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        // invalid data type
        if (!$viewData instanceof ImageDto) {
            throw new UnexpectedTypeException($viewData, ImageDto::class);
        }

        $forms = iterator_to_array($forms);
        $forms['featuredImage']->setData($viewData->id);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var ?string $featuredImageId */
        $featuredImageId = $forms['featuredImage']->getData();
        $imageUuid = null;
        if ($featuredImageId !== null) {
            $imageUuid = new UuidV4($featuredImageId);
        }

        $viewData = new ImageDto($imageUuid);
    }
}
