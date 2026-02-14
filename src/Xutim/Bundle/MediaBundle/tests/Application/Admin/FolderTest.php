<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Application\Admin;

use Xutim\MediaBundle\Tests\Application\MediaApplicationTestCase;

class FolderTest extends MediaApplicationTestCase
{
    public function testCreateFolderFormLoads(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/media/folder/new');
        $this->assertResponseIsSuccessful();
    }

    public function testCreateFolderSubmit(): void
    {
        $client = $this->createAuthenticatedClient();

        $crawler = $client->request('GET', '/media/folder/new');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="media_folder"]')->form();
        $form['media_folder[name]'] = 'New Folder';
        $client->submit($form);

        $this->assertResponseRedirects();
    }

    public function testCreateSubfolderSubmit(): void
    {
        $client = $this->createAuthenticatedClient();
        $parent = $this->createFolderInDb('Parent Folder');

        $crawler = $client->request('GET', '/media/folder/new/' . $parent->id()->toRfc4122());
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="media_folder"]')->form();
        $form['media_folder[name]'] = 'Subfolder';
        $client->submit($form);

        $this->assertResponseRedirects();
    }

    public function testEditFolderFormLoads(): void
    {
        $client = $this->createAuthenticatedClient();
        $folder = $this->createFolderInDb('Existing Folder');

        $client->request('GET', '/media/folder/' . $folder->id()->toRfc4122() . '/edit');
        $this->assertResponseIsSuccessful();
    }

    public function testEditFolderSubmit(): void
    {
        $client = $this->createAuthenticatedClient();
        $folder = $this->createFolderInDb('Original Name');

        $crawler = $client->request('GET', '/media/folder/' . $folder->id()->toRfc4122() . '/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="media_folder"]')->form();
        $form['media_folder[name]'] = 'Renamed Folder';
        $client->submit($form);

        $this->assertResponseRedirects();
    }
}
