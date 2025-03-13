<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Validator;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Repository\UserRepository;
use Xutim\CoreBundle\Validator\UniqueUsername;
use Xutim\CoreBundle\Validator\UniqueUsernameValidator;

/**
*
 * @extends ConstraintValidatorTestCase<UniqueUsernameValidator>
*/
class UniqueUsernameValidatorTest extends ConstraintValidatorTestCase
{
    private MockObject&UserRepository $repo;

    protected function createValidator(): UniqueUsernameValidator
    {
        $this->repo = $this->createMock(UserRepository::class);
        return new UniqueUsernameValidator($this->repo);
    }

    public function testValidateWithNonUniqueUsername(): void
    {
        $name = 'existing';
        $constraint = new UniqueUsername();

        $this->repo->expects($this->once())
            ->method('isNameUsed')
            ->with($name)
            ->willReturn(true);

        $this->validator->validate($name, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $name)
            ->assertRaised();
    }

    public function testValidateWithUniqueUsername(): void
    {
        $name = 'new';
        $constraint = new UniqueUsername();

        $this->repo->expects($this->once())
            ->method('isNameUsed')
            ->with($name)
            ->willReturn(false);

        $this->validator->validate($name, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateWithExistingUserEditing(): void
    {
        $name = 'test';
        $user = new User(
            Uuid::v4(),
            'existing@example.com',
            $name,
            'test',
            [],
            ['en'],
            'test'
        );
        $constraint = new UniqueUsername();
        $constraint->existingUser = $user;

        $this->repo->expects($this->once())
            ->method('isNameUsed')
            ->with($name)
            ->willReturn(true);

        $this->validator->validate($name, $constraint);

        $this->assertNoViolation();
    }
}
