<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Xutim\SecurityBundle\DataFixtures\LoadUserFixture;
use Xutim\SecurityBundle\Domain\Model\UserInterface;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;

abstract class AdminApplicationTestCase extends WebTestCase
{
    protected function createAuthenticatedClient(): KernelBrowser
    {
        $client = static::createClient();
        $client->loginUser($this->getTestUser());

        return $client;
    }

    protected function getTestUser(): UserInterface
    {
        $repository = static::getContainer()->get(UserRepositoryInterface::class);

        return $repository->findOneByEmail(LoadUserFixture::USER_EMAIL);
    }
}
