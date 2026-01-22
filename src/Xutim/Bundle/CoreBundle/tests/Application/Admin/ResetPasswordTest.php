<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Xutim\SecurityBundle\DataFixtures\LoadUserFixture;

class ResetPasswordTest extends WebTestCase
{
    public function testForgotPasswordAction(): void
    {
        $client = static::createClient();
        $client->enableProfiler();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->getConnection()->executeStatement('DELETE FROM app_reset_password_request');

        $crawler = $client->request('GET', '/admin/login');
        $this->assertResponseIsSuccessful();

        $link = $crawler->filter('#reset-password-link')->link();
        $client->click($link);
        $this->assertResponseIsSuccessful();

        $client->submitForm('send-password-reset-email-button', [
            'reset_password_request_form[email]' => LoadUserFixture::USER_EMAIL,
        ]);

        $this->assertResponseRedirects();
        $this->assertEmailCount(1);
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }
}
