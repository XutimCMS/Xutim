<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Xutim\EditorBundle\Entity\Block\HeadingBlock;

/**
 * @extends AbstractType<HeadingBlock>
 */
final class HeadingBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('html', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Heading text...',
                ],
            ])
            ->add('level', ChoiceType::class, [
                'choices' => [
                    'H1' => 1,
                    'H2' => 2,
                    'H3' => 3,
                    'H4' => 4,
                    'H5' => 5,
                    'H6' => 6,
                ],
                'expanded' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HeadingBlock::class,
        ]);
    }
}
