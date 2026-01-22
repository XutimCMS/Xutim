<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Xutim\SecurityBundle\DataFixtures\LoadUserFixture;

class ResetPasswordTest extends WebTestCase
{
    public function testForgotPasswordAction(): void
    {
        $this->markTestSkipped('Email assertion requires messenger test transport configuration');

        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/login');
        $this->assertResponseIsSuccessful();

        $link = $crawler->filter('#reset-password-link')->link();
        $client->click($link);
        $this->assertResponseIsSuccessful();

        $client->submitForm('send-password-reset-email-button', [
            'reset_password_request_form[email]' => LoadUserFixture::USER_EMAIL,
        ]);

        $this->assertEmailCount(1);
    }
}
