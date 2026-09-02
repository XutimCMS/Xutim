<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\BlockItemProvider;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Xutim\CoreBundle\Config\Layout\Block\Option\ArticleBlockItemOption;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;
use Xutim\CoreBundle\Service\ReferenceTranslationResolver;

final readonly class ArticleBlockItemProvider implements BlockItemProviderInterface
{
    public function __construct(
        private string $articleClass,
        private ContentContext $contentContext,
        private ReferenceTranslationResolver $referenceTranslationResolver,
    ) {
    }

    public function getOptionClass(): string
    {
        return ArticleBlockItemOption::class;
    }

    /** @param FormBuilderInterface<mixed> $builder */
    public function buildFormFields(FormBuilderInterface $builder): void
    {
        $locale = $this->contentContext->getLanguage();

        $builder->add('article', EntityType::class, [
            'class' => $this->articleClass,
            'label' => new TranslatableMessage('article', [], 'admin'),
            'required' => false,
            'choice_value' => 'id',
            'choice_label' => function (ArticleInterface $article) use ($locale): string {
                /** @var ContentTranslationInterface $translation */
                $translation = $this->referenceTranslationResolver->resolveByLocale($article, $locale);
                if ($translation->getLocale() === $locale) {
                    return $translation->getTitle();
                }

                return sprintf('%s (%s)', $translation->getTitle(), $translation->getLocale());
            },
        ]);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapDataToForms(BlockItemDto $dto, array $forms): void
    {
        if (!array_key_exists('article', $forms)) {
            return;
        }
        $forms['article']->setData($dto->article);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapFormsToData(array $forms, BlockItemDto $dto): void
    {
        if (!array_key_exists('article', $forms)) {
            return;
        }
        /** @var ArticleInterface|null $article */
        $article = $forms['article']->getData();
        $dto->article = $article;
    }
}
