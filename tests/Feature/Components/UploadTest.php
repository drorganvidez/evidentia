<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

class UploadTest extends ComponentTestCase
{
    protected function tearDown(): void
    {
        View::share('errors', new ViewErrorBag);

        parent::tearDown();
    }

    public function test_the_zone_wraps_a_real_file_input_named_by_the_label(): void
    {
        $html = $this->render('<x-ui.upload url="/upload" revert-url="/upload/revert" remove-url="/upload/remove/NAME" />');

        $this->assertStringContainsString('x-data="upload"', $html);
        $this->assertStringContainsString('<label for="field-uploaded"', $html);
        $this->assertMatchesRegularExpression('/<input id="field-uploaded" type="file" name="uploaded\[\]"/', $html);
        $this->assertMatchesRegularExpression('/<input [^>]*\smultiple[\s>]/', $html);
        $this->assertStringContainsString('accept="image/*,.pdf,.zip,.rar,.7z,.tar,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.odt,.ods,.odp"', $html);
        $this->assertStringContainsString('x-ref="input"', $html);
        $this->assertStringContainsString('x-on:change="onPick"', $html);
        $this->assertStringContainsString('x-on:drop.prevent="onDrop"', $html);
        $this->assertStringContainsString('x-bind:data-state="dragging ? \'dragging\' : \'idle\'"', $html);
        $this->assertStringContainsString('x-bind:aria-busy="uploading ? \'true\' : \'false\'"', $html);
        $this->assertStringContainsString('<strong>Arrastra aquí el documento</strong> o pulsa para elegirlo', $html);
        $this->assertStringContainsString('<span id="field-uploaded-hint" class="text-md text-ink-muted">PDF, imágenes o ZIP · hasta 50 MB por archivo</span>', $html);
        $this->assertMatchesRegularExpression('/<input [^>]*aria-describedby="field-uploaded-hint"/', $html);
        $this->assertStringContainsString('<template x-for="file in files" x-bind:key="file.key">', $html);
        $this->assertStringContainsString('<template x-if="file.status === \'done\'">', $html);
        $this->assertStringContainsString('x-bind:name="hiddenName"', $html);
        $this->assertStringContainsString('<p role="status" class="sr-only" x-text="summary"></p>', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('<progress max="100"', $html);
        $this->assertStringContainsString('x-on:click="remove(file.key)"', $html);
        $this->assertStringContainsString('x-on:click="retry(file.key)"', $html);
        $this->assertStringNotContainsString('_token', $html);
        $this->assertStringNotContainsString('aria-invalid', $html);
        $this->assertNoAutofocus($html);
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_the_configuration_travels_escaped_in_a_data_attribute(): void
    {
        $html = $this->render('<x-ui.upload url="/upload?a=1&b=2" revert-url="/upload/revert" remove-url="/upload/remove/NAME" :max-files="3" :max-size="1024" :max-total="0" accept=".pdf" />');

        $this->assertStringContainsString('data-config="{&quot;name&quot;:&quot;uploaded[]&quot;,&quot;url&quot;:&quot;\/upload?a=1&amp;b=2&quot;,&quot;revertUrl&quot;:&quot;\/upload\/revert&quot;,&quot;removeUrl&quot;:&quot;\/upload\/remove\/NAME&quot;,&quot;accept&quot;:&quot;.pdf&quot;,&quot;multiple&quot;:true,&quot;maxFiles&quot;:3,&quot;maxSize&quot;:1024,&quot;maxTotal&quot;:0}"', $html);
        $this->assertStringContainsString('&quot;maxTotal&quot;:209715200', $this->render('<x-ui.upload url="/upload" />'));
        $this->assertStringContainsString('accept=".pdf"', $html);
    }

    public function test_the_initial_files_are_normalised_to_objects(): void
    {
        $html = $this->render('<x-ui.upload url="/upload" :files="$files" />', [
            'files' => ['informe.pdf', ['name' => 'acta.pdf', 'size' => 2048, 'id' => 'abc']],
        ]);

        $this->assertStringContainsString('data-files="[{&quot;name&quot;:&quot;informe.pdf&quot;,&quot;size&quot;:null,&quot;id&quot;:null},{&quot;name&quot;:&quot;acta.pdf&quot;,&quot;size&quot;:2048,&quot;id&quot;:&quot;abc&quot;}]"', $html);
        $this->assertStringContainsString('data-files="[]"', $this->render('<x-ui.upload url="/upload" />'));
    }

    public function test_a_single_file_zone_has_no_multiple_and_a_custom_label(): void
    {
        $html = $this->render('<x-ui.upload name="import" url="/upload" :multiple="false" accept=".xls,.xlsx" label="Arrastra aquí la hoja de cálculo o pulsa para elegirla" hint="Excel" capture="environment" />');

        $this->assertDoesNotMatchRegularExpression('/<input [^>]*\smultiple[\s>]/', $html);
        $this->assertStringContainsString('&quot;multiple&quot;:false', $html);
        $this->assertStringContainsString('<label for="field-import"', $html);
        $this->assertStringContainsString('name="import"', $html);
        $this->assertStringContainsString('<strong>Arrastra aquí la hoja de cálculo</strong> o pulsa para elegirla', $html);
        $this->assertStringContainsString('capture="environment"', $html);
        $this->assertStringContainsString('>Excel</span>', $html);
    }

    public function test_the_error_comes_from_the_prop_or_the_bag_and_the_hint_can_be_dropped(): void
    {
        $prop = $this->render('<x-ui.upload url="/upload" error="Adjunta al menos un archivo" hint="" />');

        $this->assertStringContainsString('<p id="field-uploaded-error" class="flex items-center gap-1.5 text-sm text-rejected-ink">', $prop);
        $this->assertStringContainsString('Adjunta al menos un archivo', $prop);
        $this->assertMatchesRegularExpression('/<input [^>]*aria-invalid="true"/', $prop);
        $this->assertMatchesRegularExpression('/<input [^>]*aria-describedby="field-uploaded-error"/', $prop);
        $this->assertStringNotContainsString('field-uploaded-hint', $prop);
        $this->assertUniqueIds($prop);

        $this->withViewErrors(['uploaded' => 'Falta el archivo', 'proof.0' => 'Tipo no admitido']);

        $this->assertStringContainsString('Falta el archivo', $this->render('<x-ui.upload url="/upload" />'));
        $this->assertStringContainsString('Tipo no admitido', $this->render('<x-ui.upload name="proof[]" url="/upload" />'));
    }

    public function test_label_and_hint_are_escaped(): void
    {
        $html = $this->render('<x-ui.upload url="/upload" :label="$label" :hint="$hint" />', [
            'label' => '<b>Suelta</b> o <i>elige</i>',
            'hint' => '<u>pista</u>',
        ]);

        $this->assertStringContainsString('<strong>&lt;b&gt;Suelta&lt;/b&gt;</strong> o &lt;i&gt;elige&lt;/i&gt;', $html);
        $this->assertStringContainsString('&lt;u&gt;pista&lt;/u&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
