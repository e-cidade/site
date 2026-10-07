<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\WordPress;

use App\Listeners\WordPress\WordPressClient;
use App\Listeners\WordPress\WordPressPostSynchronizer;
use PHPUnit\Framework\TestCase;

final class WordPressPostSynchronizerTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/ecidade-wordpress-' . bin2hex(random_bytes(6));
        mkdir($this->root . '/posts', 0775, true);
        mkdir($this->root . '/media', 0775, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->root);
    }

    public function testCreatesNewPostWithPublicWordPressMetadataAndLocalMedia(): void
    {
        $post = $this->post();
        $post['content']['rendered'] = '<p>Conteúdo.</p><img src="https://ecidade.softwarepublico.org/wp-content/uploads/body.png" srcset="https://ecidade.softwarepublico.org/wp-content/uploads/body.png 1x">';
        $client = new FakeSynchronizerWordPressClient([$post]);
        $synchronizer = new WordPressPostSynchronizer($client, $this->root . '/posts', $this->root . '/media');

        $summary = $synchronizer->synchronize();
        $content = file_get_contents($this->root . '/posts/nova-noticia.md');

        self::assertSame(1, $summary['created']);
        self::assertSame(0, $summary['updated']);
        self::assertSame(2, $summary['media']);
        self::assertStringContainsString('wordpress_id: 123', (string) $content);
        self::assertStringContainsString('wordpress_modified: "2026-10-07T15:30:00+00:00"', (string) $content);
        self::assertStringContainsString('cover_image: "/assets/images/migrated/nova-noticia-cover.jpg"', (string) $content);
        self::assertStringContainsString('src="/assets/images/migrated/nova-noticia-1.png"', (string) $content);
        self::assertStringContainsString('srcset="/assets/images/migrated/nova-noticia-1.png 1x"', (string) $content);
        self::assertFileExists($this->root . '/media/nova-noticia-cover.jpg');
        self::assertFileExists($this->root . '/media/nova-noticia-1.png');
    }

    public function testDoesNotOverwriteManuallyMigratedPostWithSameSlug(): void
    {
        file_put_contents($this->root . '/posts/nova-noticia.md', "---\ntitle: Manual\n---\nManual\n");
        $client = new FakeSynchronizerWordPressClient([$this->post()]);
        $synchronizer = new WordPressPostSynchronizer($client, $this->root . '/posts', $this->root . '/media');

        $summary = $synchronizer->synchronize();

        self::assertSame(1, $summary['skipped']);
        self::assertSame("---\ntitle: Manual\n---\nManual\n", file_get_contents($this->root . '/posts/nova-noticia.md'));
        self::assertFileDoesNotExist($this->root . '/media/nova-noticia-cover.jpg');
    }

    public function testUpdatesPostPreviouslyManagedBySynchronizer(): void
    {
        file_put_contents($this->root . '/posts/nova-noticia.md', "---\nwordpress_id: 123\n---\nOld\n");
        $client = new FakeSynchronizerWordPressClient([$this->post()]);
        $synchronizer = new WordPressPostSynchronizer($client, $this->root . '/posts', $this->root . '/media');

        $summary = $synchronizer->synchronize();

        self::assertSame(1, $summary['updated']);
        self::assertStringContainsString('Nova notícia', (string) file_get_contents($this->root . '/posts/nova-noticia.md'));
    }

    public function testRenamesManagedPostWhenWordPressSlugChanges(): void
    {
        file_put_contents($this->root . '/posts/slug-antigo.md', "---\nwordpress_id: 123\n---\nOld\n");
        $client = new FakeSynchronizerWordPressClient([$this->post()]);
        $synchronizer = new WordPressPostSynchronizer($client, $this->root . '/posts', $this->root . '/media');

        $summary = $synchronizer->synchronize();

        self::assertSame(1, $summary['updated']);
        self::assertFileDoesNotExist($this->root . '/posts/slug-antigo.md');
        self::assertFileExists($this->root . '/posts/nova-noticia.md');
    }

    /** @return array<string, mixed> */
    private function post(): array
    {
        return [
            'id' => 123,
            'slug' => 'nova-noticia',
            'date' => '2026-10-07T12:00:00',
            'modified_gmt' => '2026-10-07T15:30:00',
            'link' => 'https://ecidade.softwarepublico.org/nova-noticia/',
            'title' => ['rendered' => 'Nova notícia'],
            'excerpt' => ['rendered' => '<p>Resumo da notícia.</p>'],
            'content' => ['rendered' => '<p>Conteúdo <strong>WordPress</strong>.</p>'],
            '_embedded' => [
                'author' => [['name' => 'administrador']],
                'wp:term' => [[['taxonomy' => 'category', 'name' => 'Notícias']]],
                'wp:featuredmedia' => [[
                    'source_url' => 'https://ecidade.softwarepublico.org/wp-content/uploads/2026/10/capa.jpg',
                    'alt_text' => 'Imagem da notícia',
                ]],
            ],
        ];
    }

    private function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (array_diff(scandir($directory) ?: [], ['.', '..']) as $entry) {
            $path = $directory . '/' . $entry;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}

final class FakeSynchronizerWordPressClient extends WordPressClient
{
    /** @param list<array<string, mixed>> $posts */
    public function __construct(private readonly array $posts)
    {
        parent::__construct('https://example.test');
    }

    public function fetchPosts(): array
    {
        return $this->posts;
    }

    public function fetchBinary(string $url): string
    {
        return 'binary:' . $url;
    }
}
