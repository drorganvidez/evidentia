<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\File;

class BaseLayoutTest extends ComponentTestCase
{
    public function test_the_head_carries_the_csrf_token_the_brand_and_the_manifest(): void
    {
        $html = $this->render('<x-layout.base title="Prueba">Hola</x-layout.base>');

        $this->assertStringContainsString('<meta name="csrf-token" content="'.csrf_token().'">', $html);
        $this->assertStringContainsString('<title>Prueba · '.config('app.name').'</title>', $html);
        $this->assertStringContainsString('href="'.asset('favicon.svg').'" type="image/svg+xml"', $html);
        $this->assertStringContainsString('href="'.asset('favicon.ico').'" sizes="32x32"', $html);
        $this->assertStringContainsString('rel="apple-touch-icon"', $html);
        $this->assertStringContainsString('rel="manifest" href="'.asset('site.webmanifest').'"', $html);
        $this->assertStringContainsString('<meta property="csp-nonce"', $html);
        $this->assertStringContainsString('Hola', $html);
        $this->assertCspSafe($html);
    }

    public function test_the_entry_point_can_be_swapped_for_the_public_one(): void
    {
        $this->withVite();
        $this->app->usePublicPath($this->fakeBuild());

        $html = $this->render('<x-layout.base entry="resources/js/public.js">Hola</x-layout.base>');

        $this->assertStringContainsString('assets/public-1.js', $html);
        $this->assertStringNotContainsString('assets/app-1.js', $html);
    }

    public function test_the_brand_files_exist_and_are_well_formed(): void
    {
        $this->assertStringContainsString('linearGradient', File::get(public_path('favicon.svg')));
        $this->assertStringContainsString('#7d6af5', File::get(public_path('favicon.svg')));
        $this->assertGreaterThan(0, File::size(public_path('favicon.ico')));

        $manifest = json_decode(File::get(public_path('site.webmanifest')), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('Evidentia', $manifest['name']);
        $this->assertSame('es', $manifest['lang']);

        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path($icon['src']));
        }
    }

    private function fakeBuild(): string
    {
        $tmp = sys_get_temp_dir().'/evidentia-layout-'.uniqid();
        File::ensureDirectoryExists($tmp.'/build');
        File::put($tmp.'/build/manifest.json', json_encode([
            'resources/css/app.css' => ['file' => 'assets/app-1.css', 'src' => 'resources/css/app.css', 'isEntry' => true],
            'resources/js/app.js' => ['file' => 'assets/app-1.js', 'src' => 'resources/js/app.js', 'isEntry' => true],
            'resources/js/public.js' => ['file' => 'assets/public-1.js', 'src' => 'resources/js/public.js', 'isEntry' => true],
        ]));

        return $tmp;
    }
}
