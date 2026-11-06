<?php

namespace Tests\Feature\Components;

use Illuminate\View\ViewException;
use InvalidArgumentException;

class SegmentedTest extends ComponentTestCase
{
    /**
     * @return list<array<string, mixed>>
     */
    private function types(): array
    {
        return [['value' => 1, 'label' => 'Ordinaria'], ['value' => 2, 'label' => 'Extraordinaria']];
    }

    public function test_it_is_a_fieldset_of_radios_with_a_hidden_legend_and_unique_ids(): void
    {
        $html = $this->render('<x-ui.segmented name="type" legend="Tipo de reunión" :options="$options" :value="1" class="mt-2" />', ['options' => $this->types()]);

        // The fieldset is where a link of the error summary lands
        $this->assertMatchesRegularExpression('/^<fieldset\s+id="field-type" tabindex="-1" class="grid gap-1.5 justify-items-start mt-2">/', trim($html));
        $this->assertStringContainsString('<legend class="sr-only">Tipo de reunión</legend>', $html);
        $this->assertStringContainsString('<div class="inline-flex max-w-full gap-0.5 overflow-x-auto rounded-full border border-glass-edge bg-field p-0.75 no-scrollbar">', $html);
        $this->assertSame(2, substr_count($html, 'type="radio" name="type"'));
        $this->assertMatchesRegularExpression('/<input type="radio" name="type" value="1" id="field-type-1"\s+class="peer sr-only" checked\s*>/', $html);
        $this->assertMatchesRegularExpression('/<input type="radio" name="type" value="2" id="field-type-2"\s+class="peer sr-only"\s*>/', $html);
        $this->assertStringContainsString('peer-checked:bg-glass-2 peer-checked:text-ink peer-checked:shadow-raised peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-hi pointer-coarse:min-h-11 min-h-7.5 px-2.75 text-xs', $html);
        $this->assertStringContainsString('Ordinaria', $html);
        $this->assertStringContainsString('Extraordinaria', $html);
        $this->assertStringNotContainsString('aria-describedby', $html);
        $this->assertStringNotContainsString('title=', $html);
        $this->assertUniqueIds($html);
        $this->assertNoAutofocus($html);
        $this->assertCspSafe($html);
    }

    public function test_the_visible_box_comes_after_the_input_inside_the_label(): void
    {
        $html = $this->render('<x-ui.segmented name="type" legend="Tipo" :options="$options" />', ['options' => $this->types()]);

        $this->assertLessThan(strpos($html, '<span class="inline-flex'), strpos($html, '<input type="radio"'), 'peer-* needs the input first');
        $this->assertMatchesRegularExpression('/<label class="relative"\s*>\s*<input/', $html);
    }

    public function test_old_input_wins_over_the_value(): void
    {
        $this->withSession(['_old_input' => ['type' => '2']]);
        $this->app['request']->setLaravelSession($this->app['session.store']);

        $html = $this->render('<x-ui.segmented name="type" legend="Tipo" :options="$options" :value="1" />', ['options' => $this->types()]);

        $this->assertMatchesRegularExpression('/value="2" id="field-type-2"\s+class="peer sr-only" checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="1" id="field-type-1"\s+class="peer sr-only" checked/', $html);
    }

    public function test_without_a_value_nothing_is_checked(): void
    {
        $this->assertDoesNotMatchRegularExpression('/sr-only" checked/', $this->render('<x-ui.segmented name="type" legend="Tipo" :options="$options" />', ['options' => $this->types()]));
    }

    public function test_icon_only_hides_the_text_and_adds_a_title(): void
    {
        $html = $this->render('<x-ui.segmented name="modality" legend="Modalidad" :options="$options" value="online" icon-only />', [
            'options' => [['value' => 'onsite', 'label' => 'Presencial', 'icon' => 'map-pin'], ['value' => 'online', 'label' => 'Virtual', 'icon' => 'video']],
        ]);

        $this->assertSame(2, preg_match_all('/<label class="relative"\s+title="[^"]+"\s*>/', $html));
        $this->assertStringContainsString('title="Presencial"', $html);
        $this->assertStringContainsString('<span class="sr-only">Presencial</span>', $html);
        $this->assertStringNotContainsString('<span>Presencial</span>', $html);
        $this->assertStringContainsString('size-7.5 justify-center pointer-coarse:min-w-11', $html);
        $this->assertSame(2, substr_count($html, '<svg'));
        $this->assertStringContainsString('class="shrink-0 size-4"', $html);
        $this->assertMatchesRegularExpression('/id="field-modality-online"\s+class="peer sr-only" checked/', $html);
    }

    public function test_the_error_is_linked_to_the_fieldset(): void
    {
        $html = $this->render('<x-ui.segmented name="type" legend="Tipo" :options="$options" error="Elige el tipo." />', ['options' => $this->types()]);

        $this->assertMatchesRegularExpression('/^<fieldset\s+id="field-type" tabindex="-1" class="grid gap-1.5 justify-items-start" aria-describedby="field-type-error">/', trim($html));
        $this->assertStringContainsString('<p id="field-type-error" class="text-sm font-medium text-rejected-ink">Elige el tipo.</p>', $html);
        $this->assertUniqueIds($html);
    }

    public function test_block_visible_legend_and_small_size_map_to_literal_classes(): void
    {
        $block = $this->render('<x-ui.segmented name="status" legend="Estado" :options="$options" block :hide-legend="false" size="sm" />', ['options' => $this->types()]);

        $this->assertMatchesRegularExpression('/<fieldset\s+id="field-status" tabindex="-1" class="grid gap-1.5 w-full"/', $block);
        $this->assertStringContainsString('<legend class="mb-1.5 text-sm font-semibold text-ink">Estado</legend>', $block);
        $this->assertStringContainsString('flex w-full [&>label]:flex-1 [&>label>span]:w-full [&>label>span]:justify-center', $block);
        $this->assertStringContainsString('min-h-6.5 px-2 text-2xs', $block);
        $this->assertStringNotContainsString('justify-items-start', $block);
    }

    public function test_the_slot_options_carry_their_own_attributes_to_the_input(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.segmented name="theme" legend="Tema" icon-only>
                <x-ui.segmented.option name="theme" value="light" label="Claro" icon="sun" icon-only x-bind:checked="$store.theme.is('light')" x-on:change="$store.theme.select('light')" />
                <x-ui.segmented.option name="theme" value="dark" label="Oscuro" icon="moon" icon-only checked data-test="dark" />
            </x-ui.segmented>
            BLADE);

        $this->assertMatchesRegularExpression('/<input type="radio" name="theme" value="light" id="field-theme-light"\s+class="peer sr-only"\s+x-bind:checked="\$store.theme.is\(\'light\'\)" x-on:change="\$store.theme.select\(\'light\'\)">/', $html);
        $this->assertMatchesRegularExpression('/<input type="radio" name="theme" value="dark" id="field-theme-dark"\s+class="peer sr-only" checked\s+data-test="dark">/', $html);
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_name_legend_and_the_option_fields_are_required(): void
    {
        $cases = [
            '<x-ui.segmented legend="Tipo" />' => 'The segmented control needs a name.',
            '<x-ui.segmented name="type" />' => 'The segmented control needs a legend.',
            '<x-ui.segmented name="type" legend="Tipo"><x-ui.segmented.option value="1" label="Uno" /></x-ui.segmented>' => 'The segmented option needs a name.',
            '<x-ui.segmented name="type" legend="Tipo"><x-ui.segmented.option name="type" label="Uno" /></x-ui.segmented>' => 'The segmented option needs a value.',
            '<x-ui.segmented name="type" legend="Tipo"><x-ui.segmented.option name="type" value="1" /></x-ui.segmented>' => 'The segmented option needs a label.',
        ];

        foreach ($cases as $blade => $message) {
            try {
                $this->render($blade);
                $this->fail('Rendered without a required prop: '.$blade);
            } catch (ViewException $exception) {
                while ($exception->getPrevious() !== null) {
                    $exception = $exception->getPrevious();
                }

                $this->assertInstanceOf(InvalidArgumentException::class, $exception);
                $this->assertSame($message, $exception->getMessage());
            }
        }
    }

    public function test_the_texts_are_escaped_and_the_ids_can_change(): void
    {
        $html = $this->render('<x-ui.segmented name="type[a]" legend="<b>Tipo</b>" :options="$options" id="meeting-type" error="<i>Error</i>" />', [
            'options' => [['value' => 'x y', 'label' => '<u>Uno</u>']],
        ]);

        $this->assertStringContainsString('&lt;b&gt;Tipo&lt;/b&gt;', $html);
        $this->assertStringContainsString('&lt;u&gt;Uno&lt;/u&gt;', $html);
        $this->assertStringContainsString('&lt;i&gt;Error&lt;/i&gt;', $html);
        $this->assertStringContainsString('aria-describedby="meeting-type-error"', $html);
        $this->assertMatchesRegularExpression('/^<fieldset\s+id="meeting-type" tabindex="-1"/', trim($html));
        $this->assertStringContainsString('id="field-type-a" tabindex="-1"', $this->render('<x-ui.segmented name="type[a]" legend="Tipo" :options="$options" />', ['options' => $this->types()]));
        $this->assertStringContainsString('id="field-typea-x-y"', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
