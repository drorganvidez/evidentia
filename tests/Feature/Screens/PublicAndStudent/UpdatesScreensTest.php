<?php

namespace Tests\Feature\Screens\PublicAndStudent;

/** The releases and the issues of the repository, or the notice when GitHub does not answer. */
class UpdatesScreensTest extends ScreenTestCase
{
    public function test_the_updates_show_the_releases_and_the_issues(): void
    {
        $this->signedAs('estudiante');
        $release = (object) [
            'name' => 'Versión 4.0.0', 'tag_name' => 'v4.0.0', 'published_at' => now()->subDay()->toIso8601String(),
            'body' => "Interfaz nueva\n<script>alert(1)</script>", 'html_url' => 'https://github.com/drorganvidez/evidentia/releases/v4.0.0',
            'zipball_url' => 'https://example.test/v4.zip', 'tarball_url' => 'https://example.test/v4.tar.gz',
        ];
        $issue = (object) [
            'title' => 'El acta no guarda los acuerdos', 'html_url' => 'https://github.com/drorganvidez/evidentia/issues/1',
            'created_at' => now()->subDays(2)->toIso8601String(), 'labels' => [(object) ['name' => 'bug']],
        ];

        $html = view('updates.list', ['releases' => [$release], 'issues' => [$issue]])->render();

        $this->assertStringContainsString('Versión 4.0.0', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('El acta no guarda los acuerdos', $html);
        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertCspSafe($html);
        $this->assertUniqueIds($html);

        $html = view('updates.list_error')->render();

        $this->assertStringContainsString('GitHub no responde ahora', $html);
        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertCspSafe($html);
    }
}
