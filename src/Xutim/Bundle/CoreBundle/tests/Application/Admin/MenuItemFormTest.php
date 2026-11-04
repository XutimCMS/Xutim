<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

final class MenuItemFormTest extends AdminApplicationTestCase
{
    public function testNewMenuItemFormRenders(): void
    {
        $this->createAuthenticatedClient()->request('GET', '/admin/en/menu/new-item');

        $this->assertResponseIsSuccessful();
    }
}
