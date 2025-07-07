<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ResetPasswordTest extends WebTestCase
{
    public function testForgotPasswordAction(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/login');

        $client->clickLink('I forgot password');
        $this->assertResponseIsSuccessful();

        $client->submitForm('send-password-reset-email-button', [
            'reset_password_request_form[email]' => LoadUserFixture::USER_EMAIL,
        ]);

        $this->assertEmailCount(1);
    }
}
