<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Xutim\EditorBundle\Entity\Block\ParagraphBlock;

/**
 * @extends AbstractType<ParagraphBlock>
 */
final class ParagraphBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('html', TextareaType::class, [
            'required' => false,
            'attr' => [
                'rows' => 3,
                'placeholder' => 'Enter text...',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ParagraphBlock::class,
        ]);
    }
}
