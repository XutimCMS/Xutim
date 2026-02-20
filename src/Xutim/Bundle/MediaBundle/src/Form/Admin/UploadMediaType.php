<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\File;
use Symfony\UX\Dropzone\Form\DropzoneType;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\MediaBundle\Validator\UniqueMedia;

/**
 * @extends AbstractType<array{file: UploadedFile, name: string, alt: string|null, copyright: string|null, locale: string}>
 */
final class UploadMediaType extends AbstractType
{
    public function __construct(private readonly SiteContext $siteContext)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locales = $this->siteContext->getLocales();

        $builder
            ->add('file', DropzoneType::class, [
                'label' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '20M',
                    ]),
                    new UniqueMedia(),
                ],
            ])
            ->add('name', TextType::class, [
                'label' => new TranslatableMessage('name', [], 'admin'),
                'required' => true,
            ])
            ->add('alt', TextType::class, [
                'label' => new TranslatableMessage('Alternative text', [], 'admin'),
                'help' => new TranslatableMessage('Write a short description of the image for users who rely on screen readers. Focus on what\'s important — colors, actions, setting. Example: \'A red bicycle leaning against a tree in autumn.\'', [], 'admin'),
                'required' => false,
            ])
            ->add('copyright', TextType::class, [
                'label' => new TranslatableMessage('copyright', [], 'admin'),
                'required' => false,
                'help' => new TranslatableMessage('Specify who holds the copyright for this image.', [], 'admin'),
            ])
            ->add('locale', ChoiceType::class, [
                'label' => new TranslatableMessage('language', [], 'admin'),
                'choices' => array_combine($locales, $locales),
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => static function (FormInterface $form): array {
                return ['Default'];
            },
        ]);
    }
}
