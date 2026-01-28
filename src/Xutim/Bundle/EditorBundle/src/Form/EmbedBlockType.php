<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Xutim\EditorBundle\Entity\Block\EmbedBlock;

/**
 * @extends AbstractType<EmbedBlock>
 */
final class EmbedBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('service', ChoiceType::class, [
                'choices' => [
                    'YouTube' => EmbedBlock::SERVICE_YOUTUBE,
                    'Vimeo' => EmbedBlock::SERVICE_VIMEO,
                    'Twitter' => EmbedBlock::SERVICE_TWITTER,
                    'Instagram' => EmbedBlock::SERVICE_INSTAGRAM,
                    'Other' => EmbedBlock::SERVICE_OTHER,
                ],
            ])
            ->add('source', UrlType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Paste URL here...',
                ],
            ])
            ->add('caption', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Caption (optional)',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EmbedBlock::class,
        ]);
    }
}
