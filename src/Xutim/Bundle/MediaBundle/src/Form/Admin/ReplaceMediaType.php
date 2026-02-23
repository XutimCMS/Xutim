<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\File;
use Symfony\UX\Dropzone\Form\DropzoneType;
use Xutim\MediaBundle\Validator\UniqueMedia;

/**
 * @extends AbstractType<array{file: UploadedFile}>
 */
final class ReplaceMediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var string $allowedExtension */
        $allowedExtension = $options['allowed_extension'];

        $builder
            ->add('file', DropzoneType::class, [
                'label' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '20M',
                        'extensions' => [$allowedExtension],
                    ]),
                    new UniqueMedia(),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('replace', [], 'admin'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('allowed_extension');
        $resolver->setAllowedTypes('allowed_extension', 'string');
    }
}
