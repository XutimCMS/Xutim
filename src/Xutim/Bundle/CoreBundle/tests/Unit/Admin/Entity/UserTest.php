<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Admin\Entity;

use App\Entity\Security\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class UserTest extends TestCase
{
    public function testItStoresData(): void
    {
        $id = Uuid::v4();
        $user = new User($id, 'test@example.com', 'Tomas', 'passwd', ['ROLE_SUPER_USER'], ['en'], 'data');
        $this->assertEquals($id, $user->getId());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('passwd', $user->getPassword());
        $this->assertEquals('data', $user->getAvatar());
        $this->assertEquals('Tomas', $user->getName());

        $this->assertCount(2, $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertContains('ROLE_SUPER_USER', $user->getRoles());

        $user->setRoles(['ROLE_NOT_APPROVED_USER']);
        $this->assertCount(2, $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertContains('ROLE_NOT_APPROVED_USER', $user->getRoles());

        $this->assertContains('en', $user->getTranslationLocales());
    }
}
