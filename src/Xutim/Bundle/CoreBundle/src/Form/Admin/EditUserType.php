<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Languages;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Traversable;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Dto\Admin\User\EditUserDto;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Validator\UniqueUsername;

/**
 * @template-extends AbstractType<EditUserDto>
 * @template-implements DataMapperInterface<EditUserDto>
 */
class EditUserType extends AbstractType implements DataMapperInterface
{
    public function __construct(private readonly SiteContext $siteContext)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locales = $this->siteContext->getAllLocales();
        $names = array_map(fn (string $locale) => Languages::getName($locale), $locales);
        $localeChoices = array_combine($names, $locales);
        $existingUser = $options['existing_user'];

        $builder
            ->add('name', TextType::class, [
                'label' => new TranslatableMessage('name', [], 'admin'),
                'constraints' => [
                    new Length(['min' => 3]),
                    new UniqueUsername(['existingUser' => $existingUser ])
                ]
            ])
            ->add('email', EmailType::class, [
                'disabled' => true,
                'label' => new TranslatableMessage('email', [], 'admin')
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    str_replace('ROLE_', '', User::ROLE_DEVELOPER) => User::ROLE_DEVELOPER,
                    str_replace('ROLE_', '', User::ROLE_ADMIN) => User::ROLE_ADMIN,
                    str_replace('ROLE_', '', User::ROLE_TRANSLATOR) => User::ROLE_TRANSLATOR,
                    str_replace('ROLE_', '', User::ROLE_EDITOR) => User::ROLE_EDITOR
                ],
                'choice_label' => function ($choice, string $key, mixed $value): string {
                    return $key . ' (' . match ($value) {
                        User::ROLE_DEVELOPER => new TranslatableMessage('Has full control over the CMS, including the ability to modify the code.'),
                        User::ROLE_ADMIN => new TranslatableMessage('Has full control over the CMS, except for code-related operations.'),
                        User::ROLE_TRANSLATOR => new TranslatableMessage('Can view and translate articles and pages in the assigned languages.'),
                        User::ROLE_EDITOR => new TranslatableMessage('Can create and edit articles, pages, and other types of content.'),
                        default => ''
                    } . ')';
                },
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => new TranslatableMessage('roles', [], 'admin'),
            ])
            ->add('translationLocales', LanguageType::class, [
                'label' => new TranslatableMessage('languages', [], 'admin'),
                'choices' => $localeChoices,
                'multiple' => true,
                'expanded' => false,
                'constraints' => [
                new NotNull()
                ],
                'attr' => [
                'data-controller' => 'tom-select'
                ],
                'choice_loader' => null

        ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin')
        ])
            ->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'existing_user' => null
        ]);

        $resolver->setAllowedTypes('existing_user', ['null', User::class]);
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if (!$viewData instanceof EditUserDto) {
            throw new UnexpectedTypeException($viewData, EditUserDto::class);
        }

        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['name']->setData($viewData->name);
        $forms['email']->setData($viewData->email);
        $forms['roles']->setData($viewData->roles);
        $forms['translationLocales']->setData($viewData->translationLocales);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var string $name */
        $name = $forms['name']->getData();
        /** @var string $email */
        $email = $forms['email']->getData();
        /** @var list<string> $roles */
        $roles = $forms['roles']->getData();
        /** @var list<string> $locales */
        $locales = $forms['translationLocales']->getData();

        $viewData = new EditUserDto($name, $email, $roles, $locales);
    }
}
