<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\File;

class LayoutPublicTest extends ComponentTestCase
{
    public function test_it_frames_the_content_with_the_brand_the_theme_the_notices_and_the_compact_footer(): void
    {
        $html = $this->render('<x-layout.public title="Acceso"><h1>Entrar</h1></x-layout.public>');

        $this->assertStringContainsString('<title>Acceso · '.config('app.name').'</title>', $html);
        $this->assertStringContainsString('<a href="#main"', $html);
        $this->assertStringContainsString('>Saltar al formulario</a>', $html);
        $this->assertStringContainsString('[--blob-opacity:.8] dark:[--blob-opacity:.7]', $html, 'The aurora is intense by default');
        $this->assertMatchesRegularExpression('/<header class="absolute inset-x-0 top-0 flex items-center justify-between gap-4 p-4 md:px-6 md:py-5">/', $html);
        $this->assertStringContainsString('class="size-12 shrink-0', $html, 'Large brand');
        $this->assertStringContainsString('aria-label="Evidentia, inicio"', $html);
        $this->assertSame(3, substr_count($html, 'type="radio" name="theme-public"'));
        $this->assertStringContainsString('x-data="notices"', $html);
        $this->assertStringContainsString('fixed inset-x-3 top-3 z-40', $html, 'Notices hang from the top');
        $this->assertMatchesRegularExpression('/<main id="main" tabindex="-1" class="mx-auto grid min-h-dvh w-\[min\(35rem,100%-2rem\)\] content-start items-center gap-8 pt-22 pb-18 outline-none">/', $html);
        $this->assertStringContainsString('<h1>Entrar</h1>', $html);
        $this->assertStringContainsString('print-hidden flex flex-wrap items-center justify-center gap-x-4 gap-y-1 px-4 py-4 text-xs text-ink', $html, 'Compact footer');
        $this->assertStringNotContainsString('lg:grid-cols-', $html);
        $this->assertStringNotContainsString('<div class="grid gap-6">', $html, 'No aside wrapper without the slot');

        $skip = strpos($html, 'href="#main"');
        $header = strpos($html, '<header');
        $notices = strpos($html, 'x-data="notices"');
        $main = strpos($html, '<main id="main"');
        $footer = strpos($html, '<footer');

        $this->assertTrue($skip < $header && $header < $notices && $notices < $main && $main < $footer, 'DOM order');
        $this->assertSame(1, substr_count($html, '<main '));
        $this->assertSame(1, substr_count($html, '<footer'));
        $this->assertUniqueIds($html);
        $this->assertNoAutofocus($html);
        $this->assertCspSafe($html);
    }

    public function test_wide_puts_the_aside_beside_the_content_on_large_screens(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-layout.public title="Acceso" wide subtitle="Jornadas InnoSoft 2026">
                <x-slot:aside class="content-center"><h1>Lo que haces en las jornadas, cuenta.</h1></x-slot:aside>
                <form>Formulario</form>
            </x-layout.public>
            BLADE);

        $this->assertStringContainsString('lg:w-[min(73.75rem,100%-3rem)] lg:grid-cols-[minmax(0,1.15fr)_minmax(22.5rem,28.75rem)] lg:content-center lg:gap-12 lg:py-24', $html);
        $this->assertStringContainsString('<div class="grid gap-6 content-center"><h1>Lo que haces en las jornadas, cuenta.</h1></div>', $html);
        $this->assertLessThan(strpos($html, '<form>Formulario</form>'), strpos($html, '<h1>Lo que haces'), 'The aside comes first');
        $this->assertStringContainsString('aria-label="Evidentia, Jornadas InnoSoft 2026, inicio"', $html);
        $this->assertStringContainsString('Jornadas InnoSoft 2026</span>', $html);
    }

    public function test_the_aurora_the_theme_the_content_id_the_version_and_the_footer_can_change(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-layout.public title="Firma" :intense="false" :show-theme="false" content-id="sign" version="4.0.0">
                <x-slot:footer><footer class="own">Pie propio</footer></x-slot:footer>
                <h1>Firma pública</h1>
            </x-layout.public>
            BLADE);

        $this->assertStringNotContainsString('[--blob-opacity:.8]', $html);
        $this->assertStringContainsString('fixed inset-0', $html, 'The calm aurora stays');
        $this->assertStringNotContainsString('type="radio"', $html);
        $this->assertStringContainsString('<a href="#sign"', $html);
        $this->assertStringContainsString('<main id="sign" tabindex="-1"', $html);
        $this->assertStringContainsString('<footer class="own">Pie propio</footer>', $html);
        $this->assertStringNotContainsString('Universidad de Sevilla', $html, 'The footer slot replaces the compact footer');
        $this->assertSame(1, substr_count($html, '<footer'));

        $versioned = $this->render('<x-layout.public version="4.0.0"><h1>x</h1></x-layout.public>');

        $this->assertStringContainsString('>v4.0.0</span>', $versioned);
    }

    public function test_it_loads_the_light_entry_by_default(): void
    {
        $this->withVite();
        $this->app->usePublicPath($this->fakeBuild());

        $html = $this->render('<x-layout.public><h1>x</h1></x-layout.public>');

        $this->assertStringContainsString('assets/public-1.js', $html);
        $this->assertStringNotContainsString('assets/app-1.js', $html);

        $app = $this->render('<x-layout.public entry="resources/js/app.js"><h1>x</h1></x-layout.public>');

        $this->assertStringContainsString('assets/app-1.js', $app);
    }

    private function fakeBuild(): string
    {
        $tmp = sys_get_temp_dir().'/evidentia-public-layout-'.uniqid();
        File::ensureDirectoryExists($tmp.'/build');
        File::put($tmp.'/build/manifest.json', json_encode([
            'resources/css/app.css' => ['file' => 'assets/app-1.css', 'src' => 'resources/css/app.css', 'isEntry' => true],
            'resources/js/app.js' => ['file' => 'assets/app-1.js', 'src' => 'resources/js/app.js', 'isEntry' => true],
            'resources/js/public.js' => ['file' => 'assets/public-1.js', 'src' => 'resources/js/public.js', 'isEntry' => true],
        ]));

        return $tmp;
    }
}
