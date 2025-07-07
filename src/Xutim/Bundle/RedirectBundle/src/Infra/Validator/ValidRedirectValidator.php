<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Infra\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;
use Xutim\RedirectBundle\Form\RedirectFormData;

class ValidRedirectValidator extends ConstraintValidator
{
    public function __construct(
        private readonly RedirectRepositoryInterface $repo
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof RedirectFormData || !$constraint instanceof ValidRedirect) {
            return;
        }

        $source = $value->getSource();
        $target = $value->getTarget();

        if ($source === $target) {
            $this->context->buildViolation($constraint->messageSelf)->addViolation();
            return;
        }

        foreach ($this->repo->findAll() as $existing) {
            if ($existing->getId() === $value->getId()) {
                continue;
            }

            $existingSource = $existing->getSource();
            $existingTarget = $value->getTarget();

            // Loop detection
            if ($existingSource === $target && $existingTarget === $source) {
                $this->context->buildViolation($constraint->messageLoop)
                    ->setParameter('{{ target }}', $target)
                    ->addViolation();
                break;
            }

            // Duplicate source detection
            if ($existingSource === $source) {
                $this->context->buildViolation($constraint->messageDuplicate)
                    ->addViolation();
                break;
            }
        }
    }
}
