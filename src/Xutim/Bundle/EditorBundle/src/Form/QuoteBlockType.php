<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Xutim\EditorBundle\Entity\Block\QuoteBlock;

/**
 * @extends AbstractType<QuoteBlock>
 */
final class QuoteBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('html', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Quote text...',
                ],
            ])
            ->add('attribution', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Attribution (optional)',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuoteBlock::class,
        ]);
    }
}
