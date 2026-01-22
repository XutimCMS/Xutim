<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Xutim\SecurityBundle\DataFixtures\LoadUserFixture;

class LoginTest extends WebTestCase
{
    public function testLoginAndRedirection(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');
        $this->assertResponseRedirects('http://localhost/admin/login');

        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        $buttonNode = $crawler->selectButton('login-submit');
        $form = $buttonNode->form();
        $form['email'] = LoadUserFixture::USER_EMAIL;
        $form['password'] = LoadUserFixture::USER_PASSWD_PLAIN;
        $client->submit($form);

        // Follow all redirects after login: /admin -> /admin/ -> /admin/{locale}/
        $client->followRedirects();
        $crawler = $client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div', LoadUserFixture::USER_NAME);
        $this->assertSelectorTextContains('div', LoadUserFixture::USER_EMAIL);
    }
}
