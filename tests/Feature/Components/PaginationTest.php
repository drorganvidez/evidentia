<?php

namespace Tests\Feature\Components;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\View\ViewException;
use InvalidArgumentException;

class PaginationTest extends ComponentTestCase
{
    private const OPTIONS = ['path' => '/evidencias', 'pageName' => 'pagina'];

    private function paginator(int $current, int $total = 128): LengthAwarePaginator
    {
        return new LengthAwarePaginator(range(1, 10), $total, 10, $current, self::OPTIONS);
    }

    /** Page numbers and ellipses (null) in the order they are printed. */
    private function elements(string $html): array
    {
        preg_match_all('/<li(?: aria-hidden="true")?[^>]*>(?:\s*<a[^>]*>(\d+)<\/a>)?/', $html, $matches);

        return array_map(fn (string $number): ?int => $number === '' ? null : (int) $number, $matches[1]);
    }

    public function test_the_current_page_is_marked_and_the_neighbours_are_linked(): void
    {
        $html = $this->render('<x-ui.pagination :paginator="$paginator" class="ml-auto" />', ['paginator' => $this->paginator(7)]);

        $this->assertStringContainsString('<nav aria-label="Paginación" class="flex flex-wrap items-center gap-3 ml-auto">', $html);
        $this->assertSame(1, substr_count($html, 'aria-current="page"'));
        $this->assertMatchesRegularExpression('/<a href="\/evidencias\?pagina=7"\s+aria-label="Página 7, actual"\s+aria-current="page"\s+class="[^"]*current:bg-brand-soft current:text-brand-soft-ink[^"]*">7<\/a>/', $html);
        $this->assertMatchesRegularExpression('/<a href="\/evidencias\?pagina=6"\s+aria-label="Ir a la página 6"\s+class="/', $html);
        $this->assertMatchesRegularExpression('/<a href="\/evidencias\?pagina=6" rel="prev" aria-label="Página anterior"\s+title="Página anterior"/', $html);
        $this->assertMatchesRegularExpression('/<a href="\/evidencias\?pagina=8" rel="next" aria-label="Página siguiente"\s+title="Página siguiente"/', $html);
        $this->assertSame([1, null, 6, 7, 8, null, 13], $this->elements($html));
        $this->assertSame(2, substr_count($html, 'before:content-[\'…\']'));
        $this->assertStringNotContainsString('aria-disabled', $html);
        $this->assertMatchesRegularExpression('/<span class="font-mono text-sm text-ink-muted tabular-nums md:hidden">\s*Página 7 de 13\s*<\/span>/', $html);
        $this->assertStringContainsString('pointer-coarse:size-11', $html);
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_the_first_and_the_last_page_disable_their_edge(): void
    {
        $first = $this->render('<x-ui.pagination :paginator="$paginator" />', ['paginator' => $this->paginator(1)]);

        $this->assertStringNotContainsString('rel="prev"', $first);
        $this->assertSame(1, substr_count($first, '<span aria-disabled="true"'));
        $this->assertStringContainsString('<a href="/evidencias?pagina=2" rel="next"', $first);
        $this->assertSame([1, 2, null, 13], $this->elements($first));

        $last = $this->render('<x-ui.pagination :paginator="$paginator" />', ['paginator' => $this->paginator(13)]);

        $this->assertStringContainsString('<a href="/evidencias?pagina=12" rel="prev"', $last);
        $this->assertStringNotContainsString('rel="next"', $last);
        $this->assertSame(1, substr_count($last, '<span aria-disabled="true"'));
        $this->assertSame([1, null, 12, 13], $this->elements($last));
    }

    public function test_the_window_widens_the_numbers_and_a_single_gap_is_printed(): void
    {
        $wide = $this->render('<x-ui.pagination :paginator="$paginator" :window="2" />', ['paginator' => $this->paginator(7)]);

        $this->assertSame([1, null, 5, 6, 7, 8, 9, null, 13], $this->elements($wide));

        $near = $this->render('<x-ui.pagination :paginator="$paginator" />', ['paginator' => $this->paginator(4)]);

        $this->assertSame([1, 2, 3, 4, 5, null, 13], $this->elements($near));

        $tight = $this->render('<x-ui.pagination :paginator="$paginator" :window="0" />', ['paginator' => $this->paginator(7)]);

        $this->assertSame([1, null, 7, null, 13], $this->elements($tight));
    }

    public function test_the_summary_prints_next_to_the_links_and_alone_with_a_single_page(): void
    {
        $paged = $this->render('<x-ui.pagination :paginator="$paginator" summary="Mostrando de la 61 a la 70 de 128" />', ['paginator' => $this->paginator(7)]);

        $this->assertStringContainsString('<span class="text-sm text-ink-muted max-md:hidden">Mostrando de la 61 a la 70 de 128</span>', $paged);

        $single = $this->render('<x-ui.pagination :paginator="$paginator" summary="6 anotaciones" class="ml-auto" />', ['paginator' => $this->paginator(1, 6)]);

        $this->assertSame('<span class="text-sm text-ink-muted ml-auto">6 anotaciones</span>', trim($single));
        $this->assertStringNotContainsString('<nav', $single);

        $this->assertSame('', trim($this->render('<x-ui.pagination :paginator="$paginator" />', ['paginator' => $this->paginator(1, 6)])));
    }

    public function test_a_simple_paginator_prints_no_numbers(): void
    {
        $html = $this->render('<x-ui.pagination :paginator="$paginator" label="Paginación del registro" />', [
            'paginator' => new Paginator(range(1, 11), 10, 2, self::OPTIONS),
        ]);

        $this->assertStringContainsString('<nav aria-label="Paginación del registro"', $html);
        $this->assertStringNotContainsString('<ul', $html);
        $this->assertStringContainsString('<a href="/evidencias?pagina=1" rel="prev"', $html);
        $this->assertStringContainsString('<a href="/evidencias?pagina=3" rel="next"', $html);
        $this->assertMatchesRegularExpression('/<span class="font-mono text-sm text-ink-muted tabular-nums">\s*Página 2\s*<\/span>/', $html);
        $this->assertStringNotContainsString('md:hidden', $html);

        $end = $this->render('<x-ui.pagination :paginator="$paginator" />', ['paginator' => new Paginator(range(1, 5), 10, 2, self::OPTIONS)]);

        $this->assertStringContainsString('rel="prev"', $end);
        $this->assertStringNotContainsString('rel="next"', $end);
    }

    public function test_it_needs_a_paginator(): void
    {
        try {
            $this->render('<x-ui.pagination :paginator="[1, 2, 3]" />');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);

            return;
        }

        $this->fail('A pagination without a paginator was rendered');
    }

    public function test_the_label_and_the_summary_are_escaped(): void
    {
        $html = $this->render('<x-ui.pagination :paginator="$paginator" :label="$label" :summary="$summary" />', [
            'paginator' => $this->paginator(2),
            'label' => '<b>Paginación</b>',
            'summary' => '<i>128</i>',
        ]);

        $this->assertStringContainsString('aria-label="&lt;b&gt;Paginación&lt;/b&gt;"', $html);
        $this->assertStringContainsString('&lt;i&gt;128&lt;/i&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringNotContainsString('<i>', $html);
    }
}
