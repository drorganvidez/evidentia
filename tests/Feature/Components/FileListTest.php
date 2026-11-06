<?php

namespace Tests\Feature\Components;

use App\Models\File;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Fluent;

class FileListTest extends ComponentTestCase
{
    private const IMAGE = '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"';

    private const ARCHIVE = 'd="M13.659 22H18a2 2 0 0 0 2-2V8a2.4 2.4 0 0 0-.706-1.706l-3.588-3.588A2.4 2.4 0 0 0 14 2H6a2 2 0 0 0-2 2v11.5"';

    private const TEXT = 'd="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"';

    private const PAPERCLIP = 'd="m16 6-8.414 8.586a2 2 0 0 0 2.829 2.829l8.414-8.586a4 4 0 1 0-5.657-5.657l-8.379 8.551a6 6 0 1 0 8.485 8.485l8.379-8.551"';

    private const FILES = <<<'PHP'
        [
            ['name' => 'informe-final.pdf', 'size' => 1258291, 'href' => '/files/1', 'type' => 'application/pdf'],
            ['name' => 'captura-photocall.png', 'size' => 655360, 'href' => '/files/2', 'type' => 'image/png'],
            ['name' => 'materiales.zip', 'size' => 3670016, 'href' => '/files/3', 'type' => 'application/zip'],
            ['name' => 'guion.docx', 'size' => 90112, 'href' => null, 'type' => null],
        ]
        PHP;

    private function list(string $props = ''): string
    {
        return $this->render('<x-ui.file-list :files="'.self::FILES.'" '.$props.' />');
    }

    public function test_it_is_a_labelled_list_with_a_download_button_named_after_each_file(): void
    {
        $html = $this->list('class="mt-4"');

        $this->assertStringContainsString('<ul aria-label="Archivos adjuntos" class="grid gap-2 mt-4">', $html);
        $this->assertSame(4, substr_count($html, '<li class="glass-2 flex min-w-0 items-center gap-3 rounded-lg px-4 py-3 text-md">'));
        $this->assertStringContainsString('<span class="min-w-0 flex-1 truncate" title="informe-final.pdf">informe-final.pdf</span>', $html);
        $this->assertStringContainsString('aria-label="Descargar informe-final.pdf" title="Descargar informe-final.pdf"', $html);
        $this->assertStringContainsString('aria-label="Descargar materiales.zip"', $html);
        $this->assertSame(3, substr_count($html, '<a '), 'The file without an href has no button');
        $this->assertStringContainsString('href="/files/2"', $html);
        $this->assertStringContainsString('d="M12 15V3"', $html, 'The download icon');
        $this->assertStringContainsString('</ul>', $html);
        $this->assertUniqueIds($html);
        $this->assertNoAutofocus($html);
        $this->assertCspSafe($html);
    }

    public function test_the_size_is_readable_with_a_decimal_comma(): void
    {
        $html = $this->list();

        $this->assertStringContainsString('>1,2 MB</span>', $html);
        $this->assertStringContainsString('>640 KB</span>', $html);
        $this->assertStringContainsString('>3,5 MB</span>', $html);
        $this->assertStringContainsString('>88 KB</span>', $html);
        $this->assertStringContainsString('font-mono text-sm whitespace-nowrap text-ink-muted tabular-nums', $html);

        $size = fn (string $bytes): string => $this->render('<x-ui.file-list :files="[[\'name\' => \'a.txt\', \'size\' => '.$bytes.']]" />');

        $this->assertStringContainsString('>512 B</span>', $size('512'));
        $this->assertStringContainsString('>1 KB</span>', $size('1024'));
        $this->assertStringContainsString('>1 GB</span>', $size('1073741824'));
        $this->assertStringContainsString('>2,5 GB</span>', $size('2684354560'));
        $this->assertStringContainsString('>1.024 TB</span>', $size('1125899906842624'));
        $this->assertStringNotContainsString('tabular-nums', $size('null'));
    }

    public function test_the_icon_follows_the_type_or_the_extension(): void
    {
        $html = $this->list();

        $this->assertSame(1, substr_count($html, self::IMAGE));
        $this->assertSame(1, substr_count($html, self::ARCHIVE));
        $this->assertSame(2, substr_count($html, self::TEXT));

        $extension = fn (string $name, string $type = 'null'): string => $this->render('<x-ui.file-list :files="[[\'name\' => \''.$name.'\', \'type\' => '.$type.']]" />');

        $this->assertStringContainsString(self::IMAGE, $extension('foto.JPG'));
        $this->assertStringContainsString(self::IMAGE, $extension('foto', "'image/webp'"));
        $this->assertStringContainsString(self::ARCHIVE, $extension('copia.tar.gz'));
        $this->assertStringContainsString(self::ARCHIVE, $extension('copia', "'application/x-7z-compressed'"));
        $this->assertStringContainsString(self::TEXT, $extension('hoja.xlsx'));
        $this->assertStringContainsString('aria-hidden="true"', $html);
    }

    public function test_without_files_it_prints_the_empty_state(): void
    {
        $html = $this->render('<x-ui.file-list :files="[]" class="mt-2" />');

        $this->assertStringContainsString(self::PAPERCLIP, $html);
        $this->assertMatchesRegularExpression('/<h3 class="text-base font-bold">\s*No hay pruebas adjuntas\s*<\/h3>/', $html);
        $this->assertStringContainsString('py-6 mt-2', $html);
        $this->assertStringNotContainsString('<ul', $html);

        $custom = $this->render('<x-ui.file-list :files="[]" empty-text="Sin archivos" />');

        $this->assertStringContainsString('Sin archivos', $custom);
    }

    public function test_it_takes_collections_of_arrays_models_or_other_arrayables(): void
    {
        $html = $this->render('<x-ui.file-list :files="$files" />', [
            'files' => collect([
                4 => ['name' => 'informe-final.pdf', 'size' => 1258291, 'href' => '/files/1', 'type' => 'application/pdf'],
                9 => new Fluent(['name' => 'acta.pdf', 'size' => 2048, 'href' => '/files/2', 'type' => 'application/pdf']),
            ]),
        ]);

        $this->assertSame(2, substr_count($html, '<li class="glass-2 flex'));
        $this->assertStringContainsString('aria-label="Descargar informe-final.pdf"', $html);
        $this->assertStringContainsString('aria-label="Descargar acta.pdf"', $html);
        $this->assertStringContainsString('>2 KB</span>', $html);

        $models = $this->render('<x-ui.file-list :files="$files" />', [
            'files' => new EloquentCollection([new File(['name' => 'foto.png', 'size' => 655360, 'type' => 'image/png'])]),
        ]);

        $this->assertStringContainsString('title="foto.png"', $models);
        $this->assertStringContainsString('>640 KB</span>', $models);
        $this->assertStringContainsString(self::IMAGE, $models);

        foreach ([collect(), new EloquentCollection] as $empty) {
            $none = $this->render('<x-ui.file-list :files="$files" />', ['files' => $empty]);

            $this->assertStringContainsString(self::PAPERCLIP, $none);
            $this->assertStringNotContainsString('<ul', $none);
        }
    }

    public function test_the_labels_and_the_names_are_escaped(): void
    {
        $html = $this->render('<x-ui.file-list :files="[[\'name\' => $name, \'href\' => \'/f\']]" :label="$label" download-label="Bajar" />', [
            'name' => '<b>x</b>.pdf',
            'label' => '<i>Pruebas</i>',
        ]);

        $this->assertStringContainsString('aria-label="&lt;i&gt;Pruebas&lt;/i&gt;"', $html);
        $this->assertStringContainsString('title="&lt;b&gt;x&lt;/b&gt;.pdf"', $html);
        $this->assertStringContainsString('aria-label="Bajar &lt;b&gt;x&lt;/b&gt;.pdf"', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
