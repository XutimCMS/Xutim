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

/**
 * @extends AbstractType<array{new_file: ?UploadedFile, existing_file: ?File, name: ?string, alt: ?string, locale: ?string, copyright: ?string}>
 */
class FileOrMediaType extends AbstractType
{
    public function __construct(
        private readonly SiteContext $siteContext,
        private readonly string $fileClass,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locales = $this->siteContext->getLocales();

        $builder
                // TODO: Switch to DropzoneType after
                // https://github.com/symfony/ux/issues/486 will be fixed
            ->add('new_file', FileType::class, [
                'required' => false,
                'label' => new TranslatableMessage('New file', [], 'admin')
            ])
            ->add('existing_file', EntityType::class, [
                'class' => $this->fileClass,
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
            'help' => new TranslatableMessage('Write a short description of the image for users who rely on screen readers. Focus on what’s important — colors, actions, setting. Example: \'A red bicycle leaning against a tree in autumn.\'', [], 'admin'),
                'required' => false
            ])
            ->add('locale', ChoiceType::class, [
                'label' => new TranslatableMessage('language', [], 'admin'),
                'choices' => array_combine($locales, $locales),
                'preferred_choices' => ['en', 'fr'],
            ])
            ->add('copyright', TextType::class, [
                'label' => new TranslatableMessage('copyright', [], 'admin'),
                'required' => false,
                'help' => new TranslatableMessage('Specify who holds the copyright for this image.', [], 'admin'),
            ])
            ->add('submit', SubmitType::class)
        ;
    }
}
