<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Form;

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
use Xutim\SecurityBundle\Domain\Model\User;
use Xutim\SecurityBundle\Service\UserRoleDescriptorProviderInterface;
use Xutim\SecurityBundle\Service\UserRolesProviderInterface;
use Xutim\SecurityBundle\Validator\UniqueUsername;

/**
 * @template-extends AbstractType<EditUserFormData>
 * @template-implements DataMapperInterface<EditUserFormData>
 */
class EditUserType extends AbstractType implements DataMapperInterface
{
    public function __construct(
        private readonly SiteContext $siteContext,
        private readonly UserRolesProviderInterface $rolesProvider,
        private readonly UserRoleDescriptorProviderInterface $roleDescriptorProvider,
    ) {
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
                'choices' => $this->rolesProvider->getAvailableRoles(),
                'choice_label' => function ($choice, string $key, mixed $value): string {
                    $descriptions = $this->roleDescriptorProvider->getRoleDescriptions();
                    $valueKey = is_scalar($value) ? (string) $value : '';

                    $desc = '';
                    if ($valueKey !== '' && isset($descriptions[$valueKey]) === true) {
                        $desc = ' (' . $descriptions[$valueKey] . ')';
                    }

                    return sprintf('%s%s', $key, $desc);
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
        if (!$viewData instanceof EditUserFormData) {
            throw new UnexpectedTypeException($viewData, EditUserFormData::class);
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

        $viewData = new EditUserFormData($name, $email, $roles, $locales);
    }
}
