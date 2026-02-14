<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @template-extends AbstractType<array{copyright: ?string}>
 */
class MediaCopyrightType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('copyright', TextType::class, [
                'label' => new TranslatableMessage('copyright', [], 'admin'),
                'required' => false,
                'help' => new TranslatableMessage('Specify who holds the copyright for this image.', [], 'admin'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin'),
            ])
        ;
    }
}
