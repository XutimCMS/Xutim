<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Xutim\EditorBundle\Entity\Block\ListItemBlock;

/**
 * @extends AbstractType<ListItemBlock>
 */
final class ListItemBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('html', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'List item text...',
                ],
            ])
            ->add('listType', ChoiceType::class, [
                'choices' => [
                    'Bullet list' => ListItemBlock::LIST_TYPE_UNORDERED,
                    'Numbered list' => ListItemBlock::LIST_TYPE_ORDERED,
                    'Checklist' => ListItemBlock::LIST_TYPE_CHECKLIST,
                ],
            ])
            ->add('indent', IntegerType::class, [
                'attr' => [
                    'min' => 0,
                    'max' => 5,
                ],
            ])
            ->add('checked', CheckboxType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ListItemBlock::class,
        ]);
    }
}
