<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\SectionFieldProvider;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Xutim\CoreBundle\Config\Layout\Block\Option\ArticleBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Service\ReferenceTranslationResolver;

final readonly class ArticleSectionFieldProvider implements SectionFieldProviderInterface
{
    public function __construct(
        private string $articleClass,
        private ArticleRepository $articleRepository,
        private ContentContext $contentContext,
        private ReferenceTranslationResolver $referenceTranslationResolver,
    ) {
    }

    public function getOptionClass(): string
    {
        return ArticleBlockItemOption::class;
    }

    public function buildField(
        FormBuilderInterface|FormInterface $builder,
        string $fieldName,
        BlockItemOption $option
    ): void {
        $locale = $this->contentContext->getLanguage();

        $builder->add($fieldName, EntityType::class, [
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
            'attr' => ['data-controller' => 'tom-select'],
        ]);
    }

    public function denormalizeForForm(mixed $storedValue): mixed
    {
        if (!is_string($storedValue) || $storedValue === '') {
            return null;
        }

        return $this->articleRepository->find($storedValue);
    }

    public function normalizeForStorage(mixed $formValue): mixed
    {
        if ($formValue instanceof ArticleInterface) {
            return $formValue->getId()->toRfc4122();
        }

        return null;
    }
}
