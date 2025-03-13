<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Translation\TranslatableMessage;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Entity\File;

// use Symfony\UX\Dropzone\Form\DropzoneType;
// use Symfonycasts\DynamicForms\DependentField;
// use Symfonycasts\DynamicForms\DynamicFormBuilder;


/**
 * @extends AbstractType<array{new_file: ?UploadedFile, extisting_file: ?File, name: ?string, alt: ?string, locale: ?string}>
 */
class HeaderImageType extends AbstractType
{
    public function __construct(private readonly SiteContext $siteContext)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locales = $this->siteContext->getLocales();

        //$builder = new DynamicFormBuilder($builder);
        $builder
                // TODO: Switch to DropzoneType after
                // https://github.com/symfony/ux/issues/486 will be fixed
                ->add('new_file', FileType::class, [
                    'required' => false,
                    'label' => new TranslatableMessage('New file', [], 'admin'),
                ])
                ->add('existing_file', EntityType::class, [
                    'class' => File::class,
                    'choice_label' => 'id',
                    'choice_value' => 'id',
                    'required' => false,
                     // 'multiple' => false,
                     // 'expanded' => true,
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

                ->add('locale', ChoiceType::class, [
                    'label' => new TranslatableMessage('language', [], 'admin'),
                    'choices' => array_combine($locales, $locales),
                    'preferred_choices' => ['en', 'fr'],
                ])
                ->add('submit', SubmitType::class)
        ;
    }
}
