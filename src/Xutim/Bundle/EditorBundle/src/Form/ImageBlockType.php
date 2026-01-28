<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Xutim\EditorBundle\Entity\Block\ImageBlock;

/**
 * @extends AbstractType<ImageBlock>
 */
final class ImageBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', HiddenType::class, [
                'mapped' => false,
                'attr' => [
                    'data-controller' => 'media-field',
                ],
            ])
            ->add('caption', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Image caption (optional)',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ImageBlock::class,
        ]);
    }
}
