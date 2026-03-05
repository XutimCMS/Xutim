<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DomCrawler\Crawler;

class BlockItemFormFieldsTest extends AdminApplicationTestCase
{
    /**
     * @param list<string> $expectedFields
     */
    #[DataProvider('layoutFieldsProvider')]
    public function testAddItemFormShowsExpectedFields(string $layout, array $expectedFields): void
    {
        $client = $this->createAuthenticatedClient();

        $blockId = $this->createBlock($client, $layout);
        $crawler = $client->request('GET', '/admin/en/block/add-item/' . $blockId);
        $this->assertResponseIsSuccessful();

        foreach ($expectedFields as $field) {
            $this->assertNotEmpty(
                $crawler->filter('[id$="' . $field . '"]'),
                sprintf('Field "%s" should be present in the add-item form for layout "%s"', $field, $layout),
            );
        }
    }

    /**
     * @return array<string, array{string, list<string>}>
     */
    public static function layoutFieldsProvider(): array
    {
        return [
            'full layout shows all core fields' => [
                'full',
                ['page', 'article', 'file', 'snippet', 'tag', 'mediaFolder', 'text', 'link'],
            ],
            'embed layout shows embedUrl field' => [
                'embed',
                ['embedUrl'],
            ],
            'simple layout shows no fields' => [
                'simple',
                [],
            ],
        ];
    }

    private function createBlock(Crawler|\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, string $layout): string
    {
        $uniqueId = uniqid();
        $client->request('GET', '/admin/en/block/new');
        $client->submitForm('block_submit', [
            'block[code]' => 'test-' . $uniqueId,
            'block[name]' => 'Test ' . $uniqueId,
            'block[description]' => 'Test block',
            'block[layout]' => $layout,
        ]);
        $crawler = $client->followRedirect();

        $link = $crawler->filter('tbody tr')->reduce(function (Crawler $node) use ($uniqueId) {
            return str_contains($node->text(), 'Test ' . $uniqueId);
        })->filter('a')->first()->link();
        $crawler = $client->click($link);

        $addItemHref = $crawler->filter('a[href*="add-item"]')->attr('href');
        \assert($addItemHref !== null);

        return basename($addItemHref);
    }
}
