<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\UX\Dropzone\Form\DropzoneType;
use Xutim\CoreBundle\Context\SiteContext;

/**
 * @extends AbstractType<array{file: UploadedFile, name: string, alt: string|null, language: string}>
 */
class FileType extends AbstractType
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

            ])
            ->add('name', TextType::class, [
                'label' => new TranslatableMessage('name', [], 'admin'),
                'required' => true
            ])
            ->add('alt', TextType::class, [
                'label' => new TranslatableMessage('Alternative text', [], 'admin'),
                'help' => new TranslatableMessage('Leave empty if the image is purely decorative.', [], 'admin'),
                'required' => false
            ])
            ->add('language', ChoiceType::class, [
                'label' => new TranslatableMessage('language', [], 'admin'),
                'choices' => array_combine($locales, $locales),
                'preferred_choices' => ['en', 'fr'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin'),
            ])
        ;
    }
}
