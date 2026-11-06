<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The views of the new UI never need 'unsafe-inline': no script without a nonce, no style attribute
 * and no inline event handler in any file of the component library or of the design system page.
 */
class CspSafeViewsTest extends TestCase
{
    private const DIRECTORIES = [
        'resources/views/components/ui',
        'resources/views/components/layout',
        'resources/views/components/pdf',
        'resources/views/ui',
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
                $source = File::get($file->getPathname());
                $relative = $directory.'/'.$file->getRelativePathname();

                if (preg_match('/<script(?![^>]*\snonce=)/i', $source)) {
                    $offenders[] = $relative.' (script without nonce)';
                }

                if (preg_match('/\sstyle=/i', $source)) {
                    $offenders[] = $relative.' (style attribute)';
                }

                if (preg_match('/\son[a-z]+=/i', $source)) {
                    $offenders[] = $relative.' (inline event handler)';
                }
            }
        }

        $this->assertGreaterThan(0, $scanned);
        $this->assertSame([], $offenders);
    }
}
