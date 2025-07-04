<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;

class UniqueUsernameValidator extends ConstraintValidator
{
    public function __construct(private readonly UserRepositoryInterface $repo)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var UniqueUsername $constraint */

        if (null === $value || '' === $value) {
            return;
        }
        Assert::string($value);

        $isUsed = $this->repo->isNameUsed($value);
        if ($isUsed === false) {
            return;
        }

        $existingUser = $constraint->existingUser;
        if ($existingUser !== null && $existingUser->getName() === $value) {
            // Editing an existing user.
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
