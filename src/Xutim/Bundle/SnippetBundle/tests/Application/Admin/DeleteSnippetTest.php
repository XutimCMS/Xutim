<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Tests\Application\Admin;

use App\Entity\Core\Block;
use App\Entity\Core\BlockItem;
use App\Entity\Snippet\Snippet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Xutim\CoreBundle\Tests\Application\Admin\AdminApplicationTestCase;
use Xutim\SnippetBundle\Domain\Model\SnippetCategory;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;

class DeleteSnippetTest extends AdminApplicationTestCase
{
    public function testRefusesToDeleteASnippetStillUsedByABlock(): void
    {
        $client = $this->createAuthenticatedClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $snippet = new Snippet('footer-note-' . uniqid(), '', SnippetCategory::Ui);
        $block = new Block('footer-' . uniqid(), 'Footer', '', null, 'acme-footer');
        $item = new BlockItem($block, snippet: $snippet);
        $em->persist($snippet);
        $em->persist($block);
        $em->persist($item);
        $em->flush();

        $client->request('POST', '/admin/en/snippet/delete/' . $snippet->getId()->toRfc4122(), [
            'form' => ['_token' => $this->extractCsrfToken($client, '/admin/en/snippet/edit/' . $snippet->getId()->toRfc4122())],
        ]);

        $this->assertResponseRedirects('/admin/en/snippet');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-danger', 'still used by the block ' . $block->getCode());
        $em->clear();
        $this->assertNotNull(static::getContainer()->get(SnippetRepositoryInterface::class)->findById($snippet->getId()));
    }

    public function testDeletesAnUnusedSnippet(): void
    {
        $client = $this->createAuthenticatedClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $snippet = new Snippet('unused-' . uniqid(), '', SnippetCategory::Ui);
        $em->persist($snippet);
        $em->flush();

        $client->request('POST', '/admin/en/snippet/delete/' . $snippet->getId()->toRfc4122(), [
            'form' => ['_token' => $this->extractCsrfToken($client, '/admin/en/snippet/edit/' . $snippet->getId()->toRfc4122())],
        ]);

        $this->assertResponseRedirects('/admin/en/snippet');
        $em->clear();
        $this->assertNull(static::getContainer()->get(SnippetRepositoryInterface::class)->findById($snippet->getId()));
    }

    private function extractCsrfToken(KernelBrowser $client, string $url): string
    {
        $crawler = $client->request('GET', $url);
        $node = $crawler->filter('[data-dialog-modal-csrf-token-value]')->first();
        $this->assertGreaterThan(0, $node->count());

        return (string) $node->attr('data-dialog-modal-csrf-token-value');
    }
}
