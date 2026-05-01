<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\LayoutFieldProvider;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\LocaleSwitcher;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\TagBlockItemOption;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Domain\Model\TagTranslationInterface;
use Xutim\CoreBundle\Repository\TagRepository;

final readonly class TagLayoutFieldProvider implements LayoutFieldProviderInterface
{
    public function __construct(
        private string $tagClass,
        private TagRepository $tagRepository,
        private LocaleSwitcher $localeSwitcher,
        private string $defaultLocale,
    ) {
    }

    public function getOptionClass(): string
    {
        return TagBlockItemOption::class;
    }

    public function buildField(
        FormBuilderInterface|FormInterface $builder,
        string $fieldName,
        BlockItemOption $option
    ): void {
        $locale = $this->localeSwitcher->getLocale();
        $defaultLocale = $this->defaultLocale;

        $builder->add($fieldName, EntityType::class, [
            'class' => $this->tagClass,
            'label' => $this->humanize($fieldName),
            'required' => false,
            'choice_value' => 'id',
            // Locale-aware label: default `__toString()` returns whichever
            // translation sorts first by locale (alphabetical), which hides
            // tags from users browsing in other admin locales. Prefer the
            // current admin locale, fall back to the app's default locale,
            // then to any translation if neither exists.
            'choice_label' => static function (TagInterface $tag) use ($locale, $defaultLocale): string {
                /** @var TagTranslationInterface $translation */
                $translation = $tag->getTranslationByLocale($locale)
                    ?? $tag->getTranslationByLocale($defaultLocale)
                    ?? $tag->getTranslationByLocaleOrAny($locale);
                $name = $translation->getName();

                if ($translation->getLocale() !== $locale) {
                    $name .= sprintf(' (%s)', $translation->getLocale());
                }

                return $name;
            },
            'attr' => ['data-controller' => 'tom-select'],
        ]);
    }

    public function denormalizeForForm(mixed $storedValue): mixed
    {
        if (!is_string($storedValue) || $storedValue === '') {
            return null;
        }

        return $this->tagRepository->find($storedValue);
    }

    public function normalizeForStorage(mixed $formValue): mixed
    {
        if ($formValue instanceof TagInterface) {
            return $formValue->getId()->toRfc4122();
        }

        return null;
    }

    private function humanize(string $fieldName): string
    {
        return ucfirst(str_replace('_', ' ', $fieldName));
    }
}
