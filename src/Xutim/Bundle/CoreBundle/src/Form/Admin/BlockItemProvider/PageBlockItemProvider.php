<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\BlockItemProvider;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Xutim\CoreBundle\Config\Layout\Block\Option\PageBlockItemOption;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;
use Xutim\CoreBundle\Repository\PageRepository;

final readonly class PageBlockItemProvider implements BlockItemProviderInterface
{
    public function __construct(private PageRepository $pageRepository)
    {
    }

    public function getOptionClass(): string
    {
        return PageBlockItemOption::class;
    }

    /** @param FormBuilderInterface<mixed> $builder */
    public function buildFormFields(FormBuilderInterface $builder): void
    {
        $builder->add('page', ChoiceType::class, [
            'choices' => array_flip($this->pageRepository->findAllPaths()),
            'label' => new TranslatableMessage('page', [], 'admin'),
            'required' => false,
        ]);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapDataToForms(BlockItemDto $dto, array $forms): void
    {
        if (!array_key_exists('page', $forms)) {
            return;
        }
        $forms['page']->setData($dto->page?->getId());
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapFormsToData(array $forms, BlockItemDto $dto): void
    {
        if (!array_key_exists('page', $forms)) {
            return;
        }
        /** @var ?string $pageId */
        $pageId = $forms['page']->getData();
        $dto->page = $pageId !== null ? $this->pageRepository->find($pageId) : null;
    }
}
