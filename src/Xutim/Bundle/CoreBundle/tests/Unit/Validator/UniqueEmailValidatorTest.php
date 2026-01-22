<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Validator;

use App\Entity\Security\User;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Xutim\CoreBundle\Validator\UniqueEmail;
use Xutim\CoreBundle\Validator\UniqueEmailValidator;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;

/**
*
 * @extends ConstraintValidatorTestCase<UniqueEmailValidator>
*/
class UniqueEmailValidatorTest extends ConstraintValidatorTestCase
{
    private MockObject&UserRepositoryInterface $repo;

    protected function createValidator(): UniqueEmailValidator
    {
        $this->repo = $this->createMock(UserRepositoryInterface::class);
        return new UniqueEmailValidator($this->repo);
    }

    public function testValidateWithNonUniqueEmail(): void
    {
        $email = 'existing@example.com';
        $constraint = new UniqueEmail();

        $this->repo->expects($this->once())
            ->method('isEmailUsed')
            ->with($email)
            ->willReturn(true);

        $this->validator->validate($email, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $email)
            ->assertRaised();
    }

    public function testValidateWithUniqueEmail(): void
    {
        $email = 'new@example.com';
        $constraint = new UniqueEmail();

        $this->repo->expects($this->once())
            ->method('isEmailUsed')
            ->with($email)
            ->willReturn(false);

        $this->validator->validate($email, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateWithExistingUserEditing(): void
    {
        $email = 'existing@example.com';
        $user = new User(
            Uuid::v4(),
            $email,
            'test',
            'test',
            [],
            ['en'],
            'test'
        );
        $constraint = new UniqueEmail();
        $constraint->existingUser = $user;

        $this->repo->expects($this->once())
            ->method('isEmailUsed')
            ->with($email)
            ->willReturn(true);

        $this->validator->validate($email, $constraint);

        $this->assertNoViolation();
    }
}
