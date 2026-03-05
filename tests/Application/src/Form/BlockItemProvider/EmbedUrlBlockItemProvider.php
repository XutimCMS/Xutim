<?php

declare(strict_types=1);

namespace App\Form\BlockItemProvider;

use App\Config\Block\Option\EmbedUrlBlockItemOption;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Xutim\CoreBundle\Form\Admin\BlockItemProvider\BlockItemProviderInterface;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;

final readonly class EmbedUrlBlockItemProvider implements BlockItemProviderInterface
{
    public function getOptionClass(): string
    {
        return EmbedUrlBlockItemOption::class;
    }

    /** @param FormBuilderInterface<mixed> $builder */
    public function buildFormFields(FormBuilderInterface $builder): void
    {
        $builder->add('embedUrl', TextType::class, [
            'label' => 'Embed URL',
            'required' => false,
        ]);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapDataToForms(BlockItemDto $dto, array $forms): void
    {
        if (!array_key_exists('embedUrl', $forms)) {
            return;
        }

        $forms['embedUrl']->setData($dto->extra['embedUrl'] ?? null);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapFormsToData(array $forms, BlockItemDto $dto): void
    {
        if (!array_key_exists('embedUrl', $forms)) {
            return;
        }

        /** @var string|null $embedUrl */
        $embedUrl = $forms['embedUrl']->getData();
        $dto->extra['embedUrl'] = $embedUrl;
    }
}
