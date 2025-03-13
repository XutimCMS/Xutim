<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @template-extends AbstractType<array{revision_version: string, revision_diff: string}>
 */
class RevisionListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array<string> $eventIds */
        $eventIds = $options['event_ids'];
        $ids = array_combine($eventIds, $eventIds);

        $builder
            ->add('revision_version', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices' => $ids
            ])
            ->add('revision_diff', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices' => $ids
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'method' => 'GET',
            'event_ids' => null
        ]);
    }
}
