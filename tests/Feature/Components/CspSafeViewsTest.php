<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The views of the new UI never need 'unsafe-inline': no script or style element without a nonce, no style
 * attribute (written in the markup or built in a PHP array) and no inline event handler in any file of the
 * component library or of the design system page.
 */
class CspSafeViewsTest extends TestCase
{
    private const DIRECTORIES = [
        'resources/views/components/ui',
        'resources/views/components/layout',
        'resources/views/components/pdf',
        'resources/views/ui',
    ];

    /** Pattern => offence, checked against the source of every view. */
    private const OFFENCES = [
        '/<script(?![^>]*\snonce=)/i' => 'script without nonce',
        '/<style(?![^>]*\snonce=)/i' => 'style element without nonce',
        '/\sstyle=/i' => 'style attribute',
        '/[\'"]style[\'"]\s*=>/i' => 'style attribute built in PHP',
        '/\son[a-z]+=/i' => 'inline event handler',
    ];

    public function test_the_new_views_contain_nothing_the_policy_would_block(): void
    {
        $offenders = [];
        $scanned = 0;

        foreach (self::DIRECTORIES as $directory) {
            if (! File::isDirectory(base_path($directory))) {
                continue;
            }

            foreach (File::allFiles(base_path($directory)) as $file) {
                if (! str_ends_with($file->getFilename(), '.blade.php')) {
                    continue;
                }

                $scanned++;
                $relative = $directory.'/'.$file->getRelativePathname();

                foreach ($this->offences(File::get($file->getPathname())) as $offence) {
                    $offenders[] = $relative.' ('.$offence.')';
                }
            }
        }

        $this->assertGreaterThan(0, $scanned);
        $this->assertSame([], $offenders);
    }

    public function test_the_scan_catches_every_form_of_inline_code(): void
    {
        $this->assertSame(['script without nonce'], $this->offences('<script>alert(1)</script>'));
        $this->assertSame(['style element without nonce'], $this->offences('<style>p { color: red }</style>'));
        $this->assertSame(['style attribute'], $this->offences('<p style="color: red">Hola</p>'));
        $this->assertSame(['style attribute built in PHP'], $this->offences("{{ \$attributes->merge(['style' => 'color: red']) }}"));
        $this->assertSame(['style attribute built in PHP'], $this->offences('@php $attrs = ["style" => $css]; @endphp'));
        $this->assertSame(['inline event handler'], $this->offences('<button onclick="go()">Ir</button>'));

        $this->assertSame([], $this->offences('<script nonce="{{ Vite::cspNonce() }}"></script><style nonce="{{ Vite::cspNonce() }}"></style>'));
        $this->assertSame([], $this->offences('<span x-bind:style="barStyle" data-style="x" class="styled"></span>'));
    }

    /**
     * @return list<string>
     */
    private function offences(string $source): array
    {
        $found = [];

        foreach (self::OFFENCES as $pattern => $offence) {
            if (preg_match($pattern, $source)) {
                $found[] = $offence;
            }
        }

        return $found;
    }
}
