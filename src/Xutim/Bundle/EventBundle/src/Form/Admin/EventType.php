<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Form\Admin;

use DateTimeImmutable;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Traversable;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Repository\PageRepository;

/**
 * @template-extends AbstractType<EventDto>
 * @template-implements DataMapperInterface<EventDto>
 */
class EventType extends AbstractType implements DataMapperInterface
{
    public function __construct(
        private readonly SiteContext $siteContext,
        private readonly ContentContext $contentContext,
        private readonly PageRepository $pageRepository,
        private readonly string $articleClass
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $update = array_key_exists('data', $options) === true;

        $locales = $this->siteContext->getLocales();
        $localeChoices = array_combine($locales, $locales);
        $builder
            ->add('startsAt', DateTimeType::class, [
                'label' => new TranslatableMessage('starts at', [], 'admin'),
                'input' => 'datetime_immutable',
                'required' => true,
                'format' => 'dd/MM/yyyy H:i',
                'attr' => [
                    'data-controller' => 'date-time',
                    'data-date-time-format-value' => 'd/m/Y H:i',
                    'autocomplete' => 'off'
                ],
                'html5' => false,
                'constraints' => [
                    new NotNull()
                ]

            ])
            ->add('endsAt', DateTimeType::class, [
                'label' => new TranslatableMessage('ends at', [], 'admin'),
                'input' => 'datetime_immutable',
                'required' => true,
                'format' => 'dd/MM/yyyy H:i',
                'attr' => [
                    'data-controller' => 'date-time',
                    'data-date-time-format-value' => 'd/m/Y H:i',
                    'autocomplete' => 'off'
                ],
                'html5' => false,
                'constraints' => [
                    new NotNull()
                ]
            ])
            ->add('title', TextType::class, [
                'label' => new TranslatableMessage('title', [], 'admin'),
                'required' => true,
                'constraints' => [
                    new Length(['min' => 1]),
                    new NotNull()
                ]
            ])
            ->add('location', TextType::class, [
                'label' => new TranslatableMessage('location', [], 'admin'),
                'required' => true,
                'constraints' => [
                    new Length(['min' => 3]),
                    new NotNull()
                ]
            ])

            ->add('description', TextType::class, [
                'label' => new TranslatableMessage('description', [], 'admin'),
                'required' => true,
                'constraints' => [
                    new Length(['min' => 3]),
                    new NotNull()
                ]
            ])
            ->add('locale', ChoiceType::class, [
                'label' => new TranslatableMessage('Translation reference', [], 'admin'),
                'choices' => $localeChoices,
                'disabled' => $update,
            ])

            ->add('article', EntityType::class, [
                'class' => $this->articleClass,
                'label' => new TranslatableMessage('article', [], 'admin'),
                'required' => false,
                'choice_value' => 'id',
                'choice_label' => function (ArticleInterface $article) {
                    return sprintf(
                        '%s',
                        $article->getTitle()
                    );
                },
                'attr' => [
                    'data-controller' => 'tom-select',
                ]
            ])
            ->add('page', ChoiceType::class, [
                'choices' => array_flip($this->pageRepository->findAllPaths()),
                'label' => new TranslatableMessage('page', [], 'admin'),
                'required' => false,
                'attr' => [
                    'data-controller' => 'tom-select',
                ]
            ])
            ->add('submit', SubmitType::class)
            ->setDataMapper($this);
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            $forms = iterator_to_array($forms);
            $locale = $this->contentContext->getLanguage();
            $forms['locale']->setData($locale);
            return;
        }

        // invalid data type
        if (!$viewData instanceof EventDto) {
            throw new UnexpectedTypeException($viewData, EventDto::class);
        }

        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['startsAt']->setData($viewData->startsAt);
        $forms['endsAt']->setData($viewData->endsAt);
        $forms['title']->setData($viewData->title);
        $forms['location']->setData($viewData->location);
        $forms['description']->setData($viewData->description);
        $forms['locale']->setData($viewData->locale);
        $forms['article']->setData($viewData->article);
        $page = null;
        if ($viewData->page !== null) {
            $page = $this->pageRepository->find($viewData->page->getId());
            if ($page === null) {
                throw new TransformationFailedException(
                    sprintf(
                        'The selected page "%s" does not exist.',
                        $viewData->page->getId()
                    )
                );
            }
        }
        $forms['page']->setData($page);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var DateTimeImmutable $startsAt */
        $startsAt = $forms['startsAt']->getData();
        /** @var DateTimeImmutable $endsAt */
        $endsAt = $forms['endsAt']->getData();
        /** @var string $title */
        $title = $forms['title']->getData();
        /** @var string $location */
        $location = $forms['location']->getData();
        /** @var string $description */
        $description = $forms['description']->getData();
        /** @var string $locale */
        $locale = $forms['locale']->getData();
        /** @var ?ArticleInterface $article */
        $article = $forms['article']->getData();
        /** @var ?string $pageId */
        $pageId = $forms['page']->getData();

        if ($pageId === null) {
            $page = null;
        } else {
            $page = $this->pageRepository->find($pageId);
            if ($page === null) {
                throw new TransformationFailedException(
                    sprintf(
                        'The selected page "%s" does not exist.',
                        $pageId
                    )
                );
            }
        }

        $viewData = new EventDto($startsAt, $endsAt, $title, $location, $description, $locale, $article, $page);
    }
}
