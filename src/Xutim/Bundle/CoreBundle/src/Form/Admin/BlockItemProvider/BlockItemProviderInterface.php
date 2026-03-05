<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\BlockItemProvider;

use Symfony\Component\Form\FormBuilderInterface;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;

interface BlockItemProviderInterface
{
    /**
     * @return class-string<BlockItemOption>
     */
    public function getOptionClass(): string;

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    public function buildFormFields(FormBuilderInterface $builder): void;

    /**
     * @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms
     */
    public function mapDataToForms(BlockItemDto $dto, array $forms): void;

    /**
     * @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms
     */
    public function mapFormsToData(array $forms, BlockItemDto $dto): void;
}
