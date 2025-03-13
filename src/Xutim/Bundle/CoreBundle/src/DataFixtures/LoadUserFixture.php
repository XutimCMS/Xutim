<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\User;

class LoadUserFixture extends Fixture
{
    public const string USER_EMAIL = 'testuser@example.test';
    public const string USER_NAME = 'test user';
    public const string USER_UUID = 'd4bf102f-a8e1-4a2d-9cf2-192c4e7952d9';
    public const string USER_PASSWD = '$argon2id$v=19$m=65536,t=4,p=1$QjFsYXJXQU4xQk5vV0lWWg$95bL/Imstq/ZVmI1lxeyKzqLqW8CFB7winNkw/Ut0/I';
    public const string USER_AVATAR = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEEAAABBCAIAAAABlV4SAAAABnRSTlMAAAAAAABupgeRAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAiklEQVRoge3ZsQ3DMAwAwSgTZgSP4lEygkfMCFZhIQ/hrmahBxsBHK8513lMTj7rc35vZ97rn7GchgYNDRoaNDRoaNihYfzrM/egHfagoUFDg4YGDQ0aGjQ0aGjQ0KChQUODhgYNDTs0jMk59+m1NDRoaNDQoKFBQ4PbboOGBg0NGho0NGho2KHhB8Z6CZ9tt9mAAAAAAElFTkSuQmCC';
    public const string USER_PASSWD_PLAIN = 'testuser';

    public function load(ObjectManager $manager): void
    {
        $id = Uuid::fromString(self::USER_UUID);
        $user = new User(
            $id,
            self::USER_EMAIL,
            self::USER_NAME,
            self::USER_PASSWD,
            [User::ROLE_DEVELOPER],
            ['en'],
            self::USER_AVATAR
        );

        $manager->persist($user);
        $manager->flush();
    }
}
