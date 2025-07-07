<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @template-extends AbstractType<array{publishedAt: null|\DateTimeImmutable}>
 */
class PublishedDateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $constraints = $options['future_date_only'] === true ? [new GreaterThan('now')] : [];
        $builder
            ->add('publishedAt', DateTimeType::class, [
                'label' => new TranslatableMessage('published at', [], 'admin'),
                'input' => 'datetime_immutable',
                'required' => true,
                'constraints' => array_merge([new NotNull()], $constraints)
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin')
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'future_date_only' => false,
        ]);

        $resolver->setAllowedTypes('future_date_only', 'bool');
    }
}
