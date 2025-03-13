<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Traversable;
use Xutim\CoreBundle\Config\Layout\Layout;
use Xutim\CoreBundle\Form\Admin\Dto\BlockDto;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;

/**
 * @template-extends AbstractType<BlockDto>
 * @template-implements DataMapperInterface<BlockDto>
 */
class BlockType extends AbstractType implements DataMapperInterface
{
    public function __construct(
        private readonly LayoutLoader $layoutLoader
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('layout', ChoiceType::class, [
                'required' => true,
                'choices' => $this->layoutLoader->getBlockLayouts(),
                'choice_label' => fn (?Layout $item) => $item->name ?? '',
                'choice_value' => fn (?Layout $item) => $item->code ?? '',
                'choice_attr' => function (?Layout $choice, string $key, string $value) {
                    return [
                        'data-image' => $choice->image ?? ''
                    ];
                },
                'expanded' => false,
                'multiple' => false
            ])
            ->add('code', TextType::class, [
                'label' => new TranslatableMessage('code', [], 'admin'),
                'help' => 'The code will be used in twig files directly and it should be in kebab-case e.g. main-menu',
                'constraints' => [
                    new Length(['min' => 3]),
                    new NotNull(),
                    new Regex(['pattern' => '/^[a-z0-9]+(-[a-z0-9]+)*$/', 'message' => 'The code should be written in kebab-case.'])
                ]
            ])
            ->add('name', TextType::class, [
                'label' => new TranslatableMessage('name', [], 'admin'),
                'constraints' => [
                    new Length(['min' => 3]),
                    new NotNull(),
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => new TranslatableMessage('description', [], 'admin'),
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin')
            ])
            ->setDataMapper($this);
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof BlockDto) {
            throw new UnexpectedTypeException($viewData, BlockDto::class);
        }
        $layout = $this->layoutLoader->getBlockLayoutByCode($viewData->layout);

        $forms = iterator_to_array($forms);
        $forms['code']->setData($viewData->code);
        $forms['name']->setData($viewData->name);
        $forms['description']->setData($viewData->description);
        $forms['layout']->setData($layout);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var string $code */
        $code = $forms['code']->getData();
        /** @var string $name */
        $name = $forms['name']->getData();
        /** @var string $description */
        $description = $forms['description']->getData() ?? '';
        /** @var ?Layout $layout */
        $layout = $forms['layout']->getData();
        /** @var string $layoutCode */
        $layoutCode = $layout !== null ? $layout->code : '';


        $viewData = new BlockDto($code, $name, $description, null, $layoutCode);
    }
}
