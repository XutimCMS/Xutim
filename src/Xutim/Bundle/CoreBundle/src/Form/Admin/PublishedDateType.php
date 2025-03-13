<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @template-extends AbstractType<array{publishedAt: null|\DateTimeImmutable}>
 */
class PublishedDateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $update = array_key_exists('data', $options) === true;
        $builder
            ->add('publishedAt', DateTimeType::class, [
                'label' => new TranslatableMessage('published at', [], 'admin'),
                'input' => 'datetime_immutable',
                'required' => true,
                'constraints' => [
                    new NotNull()
                ],
                'attr' => [
                    'data-controller' => 'date-time'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin')
            ])
        ;
    }
}
