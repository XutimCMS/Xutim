<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Repository\UserRepository;

class UniqueEmailValidator extends ConstraintValidator
{
    public function __construct(private readonly UserRepository $repo)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var UniqueEmail $constraint */

        if (null === $value || '' === $value) {
            return;
        }
        Assert::string($value);

        $isUsed = $this->repo->isEmailUsed($value);
        if ($isUsed === false) {
            return;
        }

        $existingUser = $constraint->existingUser;
        if ($existingUser !== null && $existingUser->getEmail() === $value) {
            // Editing an existing user.
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
