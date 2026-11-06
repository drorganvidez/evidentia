<?php

namespace Tests\Feature\Screens\Meetings;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The meeting, bonus and list views never need 'unsafe-inline' nor the AdminLTE layout: no script or style without a
 * nonce, no style attribute, no inline event handler and no @extends('layouts.app') in any of their files.
 */
class MeetingViewsCspTest extends TestCase
{
    private const DIRECTORIES = ['resources/views/meeting', 'resources/views/bonus', 'resources/views/defaultlist'];

    private const OFFENCES = [
        '/<script(?![^>]*\snonce=)/i' => 'script without nonce',
        '/<style(?![^>]*\snonce=)/i' => 'style element without nonce',
        '/\sstyle=/i' => 'style attribute',
        '/[\'"]style[\'"]\s*=>/i' => 'style attribute built in PHP',
        '/\son[a-z]+=/i' => 'inline event handler',
        '/@extends\(\s*[\'"]layouts\.app[\'"]/' => 'AdminLTE layout',
    ];

    public function test_the_views_contain_nothing_the_policy_would_block(): void
    {
        $offenders = [];
        $scanned = 0;

        foreach (self::DIRECTORIES as $directory) {
            foreach (File::allFiles(base_path($directory)) as $file) {
                $scanned++;
                foreach (self::OFFENCES as $pattern => $offence) {
                    if (preg_match($pattern, File::get($file->getPathname()))) {
                        $offenders[] = $directory.'/'.$file->getRelativePathname().' ('.$offence.')';
                    }
                }
            }
        }

        $this->assertGreaterThan(20, $scanned);
        $this->assertSame([], $offenders);
    }
}
