<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Validator;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Xutim\CoreBundle\Repository\TagTranslationRepository;

class UniqueTagSlugLocaleValidator extends ConstraintValidator
{
    public function __construct(
        private readonly TagTranslationRepository $tagTransRepo
    ) {
    }

    public function validate(mixed $slug, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueTagSlugLocale) {
            throw new UnexpectedTypeException($constraint, UniqueTagSlugLocale::class);
        }
        if ($slug === null) {
            return;
        }

        $existingTranslation = $constraint->existingTranslation;

        /** @var FormInterface<array{locale: string}> $form*/
        $form = $this->context->getRoot();
        /** @var string $locale */
        $locale = $form->get('locale')->getData();
        // Skip validation if the existing entity has the same slug-locale combination
        if (
            $existingTranslation !== null &&
            $existingTranslation->getSlug() === $slug &&
            $existingTranslation->getLocale() === $locale
        ) {
            return;
        }

        if (!is_string($slug)) {
            throw new UnexpectedTypeException($slug, 'string');
        }
        $isUnique = $this->tagTransRepo->isSlugUnique(
            new UnicodeString($slug),
            $locale,
            $existingTranslation
        );
        if ($isUnique === false) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ slug }}', $slug)
                ->setParameter('{{ locale }}', $locale)
                ->addViolation();
        }
    }
}
