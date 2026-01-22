<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Core\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadSiteFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $site = new Site();
        $manager->persist($site);
        $manager->flush();
    }
}
