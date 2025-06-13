<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Form\Admin;

use DateTimeImmutable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @template-extends AbstractType<array{startsAt: DateTimeImmutable, endsAt: DateTimeImmutable}>
 */
class EventDatesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startsAt', DateTimeType::class, [
                'label' => new TranslatableMessage('starts at', [], 'admin'),
                'input' => 'datetime_immutable',
                'required' => true,
                'constraints' => [
                    new NotNull()
                ]
            ])
            ->add('endsAt', DateTimeType::class, [
                'label' => new TranslatableMessage('ends at', [], 'admin'),
                'input' => 'datetime_immutable',
                'required' => true,
                'constraints' => [
                    new NotNull()
                ]
            ])
            ->add('submit', SubmitType::class)
        ;
    }
}
