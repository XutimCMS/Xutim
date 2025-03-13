<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Public;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomepageTest extends WebTestCase
{
    public function testItDisplayHomepage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        /* $this->assertSelectorTextContains('span', 'TAIZÉ'); */
    }
}
