<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Application\Admin;

use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;
use Xutim\MediaBundle\Tests\Application\MediaApplicationTestCase;

class DeleteFolderTest extends MediaApplicationTestCase
{
    public function testDeleteEmptyFolderFormLoads(): void
    {
        $client = $this->createAuthenticatedClient();
        $folder = $this->createFolderInDb('Empty Folder');

        $client->request('GET', '/media/folder/' . $folder->id()->toRfc4122() . '/delete');
        $this->assertResponseIsSuccessful();
    }

    public function testDeleteEmptyFolderSubmit(): void
    {
        $client = $this->createAuthenticatedClient();
        $folder = $this->createFolderInDb('To Delete');
        $folderId = $folder->id();

        $crawler = $client->request('GET', '/media/folder/' . $folderId->toRfc4122() . '/delete');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();
        $client->submit($form);

        $this->assertResponseRedirects();

        /** @var MediaFolderRepositoryInterface $repo */
        $repo = static::getContainer()->get(MediaFolderRepositoryInterface::class);
        $this->assertNull($repo->findById($folderId), 'Folder should be removed from database');
    }

    public function testDeleteFolderRedirectsToParentAfterDeletion(): void
    {
        $client = $this->createAuthenticatedClient();
        $parent = $this->createFolderInDb('Parent Folder');
        $child = $this->createFolderInDb('Child Folder');
        $child->change(parent: $parent);
        $this->getEntityManager()->flush();

        $crawler = $client->request('GET', '/media/folder/' . $child->id()->toRfc4122() . '/delete');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();
        $client->submit($form);

        $this->assertResponseRedirects('/media/' . $parent->id()->toRfc4122());
    }

    public function testDeleteNonEmptyFolderWithMediaRedirects(): void
    {
        $client = $this->createAuthenticatedClient();
        $folder = $this->createFolderInDb('Folder With File');
        $this->createMediaInDb(folder: $folder);
        $this->getEntityManager()->clear();

        $client->request('GET', '/media/folder/' . $folder->id()->toRfc4122() . '/delete');

        $this->assertResponseRedirects('/media/' . $folder->id()->toRfc4122());
    }

    public function testDeleteNonEmptyFolderWithChildrenRedirects(): void
    {
        $client = $this->createAuthenticatedClient();
        $parent = $this->createFolderInDb('Parent');
        $this->createFolderInDb('Child')->change(parent: $parent);
        $this->getEntityManager()->flush();

        // Refresh to load the children collection
        $this->getEntityManager()->clear();

        $client->request('GET', '/media/folder/' . $parent->id()->toRfc4122() . '/delete');

        $this->assertResponseRedirects('/media/' . $parent->id()->toRfc4122());
    }

    public function testDeleteNonExistentFolderReturns404(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/media/folder/00000000-0000-0000-0000-000000000000/delete');
        $this->assertResponseStatusCodeSame(404);
    }
}
