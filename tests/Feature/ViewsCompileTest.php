<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use ParseError;
use PhpToken;
use Tests\TestCase;

/**
 * Every Blade view compiles to valid PHP, as php artisan view:cache does on start: a view that points at a component
 * nobody ships any more fails here, at compile time, and not on the screen of whoever opens it. The compiled code
 * stays in memory, so the parallel processes keep the compiled views they share.
 */
class ViewsCompileTest extends TestCase
{
    public function test_every_view_compiles_to_valid_php(): void
    {
        $failures = [];
        $compiled = 0;

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $compiled++;
            $relative = 'resources/views/'.$file->getRelativePathname();

            try {
                PhpToken::tokenize(Blade::compileString(File::get($file->getPathname())), TOKEN_PARSE);
            } catch (InvalidArgumentException|ParseError $error) {
                $failures[] = $relative.' ('.$error->getMessage().')';
            }
        }

        $this->assertGreaterThan(100, $compiled);
        $this->assertSame([], $failures);
    }

    public function test_a_missing_component_fails_the_compilation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to locate a class or view for component [ui.missing]');

        Blade::compileString('<x-ui.missing />');
    }

    public function test_broken_php_fails_the_parse(): void
    {
        $this->expectException(ParseError::class);

        PhpToken::tokenize(Blade::compileString('@php if ( @endphp'), TOKEN_PARSE);
    }
}
