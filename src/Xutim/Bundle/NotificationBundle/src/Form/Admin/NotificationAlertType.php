<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Xutim\NotificationBundle\Dto\Admin\Notification\NotificationAlertDto;
use Xutim\NotificationBundle\Entity\NotificationSeverity;

final class NotificationAlertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $mainLocales = $options['main_locales'];
        $extendedLocales = $options['extended_locales'];
        sort($mainLocales);
        sort($extendedLocales);

        $choices = [];
        if ($mainLocales !== []) {
            $choices['Main'] = array_combine($mainLocales, $mainLocales);
        }
        if ($extendedLocales !== []) {
            $choices['Extended'] = array_combine($extendedLocales, $extendedLocales);
        }

        $builder
            ->add('locales', ChoiceType::class, [
                'label' => new TranslatableMessage('Locales', [], 'admin'),
                'choices' => $choices,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('severity', ChoiceType::class, [
                'label' => new TranslatableMessage('Priority', [], 'admin'),
                'choices' => [
                    'Normal' => NotificationSeverity::Info,
                    'Important' => NotificationSeverity::Warning,
                    'Urgent' => NotificationSeverity::Critical,
                ],
                'choice_translation_domain' => 'admin',
            ])
            ->add('title', TextType::class, [
                'label' => new TranslatableMessage('Title', [], 'admin'),
            ])
            ->add('message', TextareaType::class, [
                'label' => new TranslatableMessage('Message', [], 'admin'),
            ])
            ->add('sendEmail', CheckboxType::class, [
                'label' => new TranslatableMessage('Send email too', [], 'admin'),
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('Notify translators', [], 'admin'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NotificationAlertDto::class,
            'main_locales' => [],
            'extended_locales' => [],
        ]);

        $resolver->setAllowedTypes('main_locales', 'array');
        $resolver->setAllowedTypes('extended_locales', 'array');
    }
}
