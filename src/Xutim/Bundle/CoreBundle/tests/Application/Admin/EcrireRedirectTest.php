<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EcrireRedirectTest extends WebTestCase
{
    public function testItRedirectsToAdmin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/ecrire');

        $this->assertResponseRedirects('http://localhost/admin');
    }
}
