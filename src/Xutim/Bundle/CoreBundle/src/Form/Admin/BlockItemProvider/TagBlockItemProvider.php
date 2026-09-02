<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\BlockItemProvider;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Xutim\CoreBundle\Config\Layout\Block\Option\TagBlockItemOption;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Domain\Model\TagTranslationInterface;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;
use Xutim\CoreBundle\Service\ReferenceTranslationResolver;

final readonly class TagBlockItemProvider implements BlockItemProviderInterface
{
    public function __construct(
        private string $tagClass,
        private ContentContext $contentContext,
        private ReferenceTranslationResolver $referenceTranslationResolver,
    ) {
    }

    public function getOptionClass(): string
    {
        return TagBlockItemOption::class;
    }

    /** @param FormBuilderInterface<mixed> $builder */
    public function buildFormFields(FormBuilderInterface $builder): void
    {
        $locale = $this->contentContext->getLanguage();

        $builder->add('tag', EntityType::class, [
            'class' => $this->tagClass,
            'label' => 'tag',
            'required' => false,
            'choice_label' => function (TagInterface $tag) use ($locale): string {
                /** @var TagTranslationInterface $translation */
                $translation = $this->referenceTranslationResolver->resolveByLocale($tag, $locale);
                if ($translation->getLocale() === $locale) {
                    return $translation->getName();
                }

                return sprintf('%s (%s)', $translation->getName(), $translation->getLocale());
            },
        ]);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapDataToForms(BlockItemDto $dto, array $forms): void
    {
        if (!array_key_exists('tag', $forms)) {
            return;
        }
        $forms['tag']->setData($dto->tag);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapFormsToData(array $forms, BlockItemDto $dto): void
    {
        if (!array_key_exists('tag', $forms)) {
            return;
        }
        /** @var TagInterface|null $tag */
        $tag = $forms['tag']->getData();
        $dto->tag = $tag;
    }
}
