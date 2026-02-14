<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

/** @extends AbstractType<array{name: string, alt: string|null}> */
final class MediaTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => new TranslatableMessage('name', [], 'admin'),
                'required' => true,
            ])
            ->add('alt', TextType::class, [
                'label' => new TranslatableMessage('Alternative text', [], 'admin'),
                'help' => new TranslatableMessage('Write a short description of the image for users who rely on screen readers. Focus on what\'s important — colors, actions, setting. Example: \'A red bicycle leaning against a tree in autumn.\'', [], 'admin'),
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
