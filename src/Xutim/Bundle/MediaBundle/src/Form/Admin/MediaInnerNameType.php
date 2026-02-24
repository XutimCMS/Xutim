<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @template-extends AbstractType<array{innerName: string}>
 */
class MediaInnerNameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('innerName', TextType::class, [
                'label' => new TranslatableMessage('inner name', [], 'admin'),
                'required' => true,
                'help' => new TranslatableMessage('A language-independent label used internally to identify this file. Not shown on the website.', [], 'admin'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin'),
            ])
        ;
    }
}
