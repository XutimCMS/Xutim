<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Xutim\CoreBundle\DataFixtures\LoadUserFixture;
use Xutim\CoreBundle\Repository\UserRepository;

class AdminApplicationTestCase extends WebTestCase
{
    protected function createAuthenticatedClient(): KernelBrowser
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail(LoadUserFixture::USER_EMAIL);

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        return $client;
    }
}
