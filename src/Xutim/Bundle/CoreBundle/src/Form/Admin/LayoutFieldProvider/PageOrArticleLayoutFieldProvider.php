<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\LayoutFieldProvider;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\PageOrArticleBlockItemOption;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\PageRepository;

/**
 * Builds a single Tom Select-backed ChoiceType listing both pages and
 * articles in grouped choices. Storage shape matches the union format:
 *   `['type' => 'page'|'article', 'value' => '<uuid>']`
 *
 * In the form the value travels as a prefixed string `page:<uuid>` or
 * `article:<uuid>` — de/normalization maps between the two shapes.
 */
final readonly class PageOrArticleLayoutFieldProvider implements LayoutFieldProviderInterface
{
    public function __construct(
        private PageRepository $pageRepository,
        private ArticleRepository $articleRepository,
    ) {
    }

    public function getOptionClass(): string
    {
        return PageOrArticleBlockItemOption::class;
    }

    public function buildField(
        FormBuilderInterface|FormInterface $builder,
        string $fieldName,
        BlockItemOption $option
    ): void {
        $choices = [];

        $pageGroup = [];
        foreach ($this->pageRepository->findAllPaths() as $id => $path) {
            $pageGroup[$path] = 'page:' . $id;
        }
        if ($pageGroup !== []) {
            $choices['Pages'] = $pageGroup;
        }

        $articleGroup = [];
        foreach ($this->articleRepository->findAllForPicker() as $id => $title) {
            $articleGroup[$title] = 'article:' . $id;
        }
        if ($articleGroup !== []) {
            $choices['Articles'] = $articleGroup;
        }

        $builder->add($fieldName, ChoiceType::class, [
            'choices' => $choices,
            'label' => $this->humanize($fieldName),
            'placeholder' => new TranslatableMessage('select page or article', [], 'admin'),
            'required' => false,
            'attr' => ['data-controller' => 'tom-select'],
        ]);
    }

    public function denormalizeForForm(mixed $storedValue): mixed
    {
        if (!is_array($storedValue)) {
            return null;
        }

        $type = $storedValue['type'] ?? null;
        $value = $storedValue['value'] ?? null;
        if (!is_string($type) || !is_string($value) || $value === '') {
            return null;
        }

        $normalizedType = $this->normalizeType($type);
        if ($normalizedType === null) {
            return null;
        }

        return $normalizedType . ':' . $value;
    }

    public function normalizeForStorage(mixed $formValue): mixed
    {
        if (!is_string($formValue) || $formValue === '') {
            return null;
        }

        $colonPos = strpos($formValue, ':');
        if ($colonPos === false) {
            return null;
        }

        $type = substr($formValue, 0, $colonPos);
        $value = substr($formValue, $colonPos + 1);

        if ($value === '' || ($type !== 'page' && $type !== 'article')) {
            return null;
        }

        return ['type' => $type, 'value' => $value];
    }

    /**
     * Accepts both the new short keys (`page`, `article`) and legacy
     * union-style FQCN short names (`PageBlockItemOption`, …) so stored
     * content written before this provider existed still loads.
     */
    private function normalizeType(string $type): ?string
    {
        return match ($type) {
            'page', 'PageBlockItemOption' => 'page',
            'article', 'ArticleBlockItemOption' => 'article',
            default => null,
        };
    }

    private function humanize(string $fieldName): string
    {
        return ucfirst(str_replace('_', ' ', $fieldName));
    }
}
