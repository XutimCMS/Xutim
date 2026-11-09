<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Tests\Application\Admin;

use Xutim\CoreBundle\Tests\Application\Admin\AdminApplicationTestCase;

final class SnippetFormTest extends AdminApplicationTestCase
{
    public function testNewSnippetFormInNonReferenceLocaleShowsTheReferenceField(): void
    {
        $crawler = $this->createAuthenticatedClient()->request('GET', '/admin/fr/snippet/new');

        $this->assertResponseIsSuccessful();
        self::assertStringContainsString('En (reference)', $crawler->filter('form')->text());
    }
}
