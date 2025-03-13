<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Length;
use Traversable;
use Xutim\CoreBundle\Dto\Admin\Page\PageMinimalDto;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Repository\PageRepository;

/**
 * @template-extends AbstractType<PageMinimalDto>
 * @template-implements DataMapperInterface<PageMinimalDto>
 */
class PageDetailsType extends AbstractType implements DataMapperInterface
{
    public function __construct(private readonly PageRepository $pageRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Page $page */
        $page = $options['page'];

        $builder
            ->add('parent', ChoiceType::class, [
                'choices' => array_flip($this->pageRepository->findAllPaths($page)),
                'label' => new TranslatableMessage('in page', [], 'admin'),
                'required' => false,
            ])
            ->add('color', ColorType::class, [
                'label' => new TranslatableMessage('color', [], 'admin'),
                'required' => false,
                'constraints' => [
                    new Length(['max' => 6])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin')
            ])
            ->setDataMapper($this);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'page' => null,
        ]);

        $resolver->setAllowedTypes('page', Page::class);
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            $forms = iterator_to_array($forms);
            return;
        }

        // invalid data type
        if (!$viewData instanceof PageMinimalDto) {
            throw new UnexpectedTypeException($viewData, PageMinimalDto::class);
        }

        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['color']->setData($viewData->color);
        $forms['parent']->setData($viewData->parent?->getId()->toRfc4122());
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var ?string $parentId */
        $parentId = $forms['parent']->getData();
        $parent = $parentId !== null ? $this->pageRepository->find($parentId) : null;

        /** @var string $color */
        $color = $forms['color']->getData();

        $viewData = new PageMinimalDto($color, [], $parent);
    }
}
