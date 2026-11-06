<?php

namespace Tests\Feature\Components;

use App\Models\Committee;
use Illuminate\View\ViewException;
use InvalidArgumentException;

class CommitteeChipTest extends ComponentTestCase
{
    private const FONT_AWESOME = '<i class="fas fa-warehouse"></i>';

    private const TRUCK = 'd="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"';

    private function committee(string $icon = self::FONT_AWESOME, int $id = 7): Committee
    {
        $committee = new Committee(['name' => 'Logística', 'icon' => $icon]);
        $committee->id = $id;

        return $committee;
    }

    public function test_it_reads_the_model_and_paints_a_dot_in_the_hue_of_its_id(): void
    {
        $html = $this->render('<x-ui.committee-chip :committee="$committee" class="ml-2" />', ['committee' => $this->committee()]);

        $this->assertStringContainsString('<span class="inline-flex items-center rounded-full glass-2 font-medium text-ink gap-1.75 py-1 pr-2.5 pl-2 text-sm ml-2">', $html);
        $this->assertStringContainsString('committee-dot hue-30', $html);
        $this->assertStringContainsString('size-2.25', $html);
        $this->assertMatchesRegularExpression('/<\/span>\s*Logística\s*<\/span>/', $html);
        $this->assertStringNotContainsString('<svg', $html);
        $this->assertStringNotContainsString('<a ', $html);
        $this->assertCspSafe($html);
    }

    public function test_the_hue_is_deterministic_by_id_and_may_be_forced(): void
    {
        foreach ([1 => 210, 2 => 60, 3 => 270, 4 => 120, 5 => 330, 6 => 180, 7 => 30, 8 => 240, 9 => 90, 12 => 0] as $id => $hue) {
            $this->assertStringContainsString('committee-dot hue-'.$hue.'"', $this->render('<x-ui.committee-chip name="x" :id="$id" />', ['id' => $id]), 'id '.$id);
        }

        $this->assertStringContainsString('committee-dot hue-0', $this->render('<x-ui.committee-chip name="Sin identificador" />'));
        $this->assertStringContainsString('committee-dot hue-150', $this->render('<x-ui.committee-chip name="x" :id="7" :hue="150" />'));
        $this->assertStringContainsString('committee-dot hue-150', $this->render('<x-ui.committee-chip :committee="$committee" :hue="150" />', ['committee' => $this->committee()]));
    }

    public function test_a_valid_lucide_name_paints_the_icon_instead_of_the_dot(): void
    {
        $model = $this->render('<x-ui.committee-chip :committee="$committee" />', ['committee' => $this->committee('truck')]);

        $this->assertStringContainsString(self::TRUCK, $model);
        $this->assertStringContainsString('aria-hidden="true"', $model);
        $this->assertStringContainsString('class="shrink-0 size-3.5 text-ink-muted"', $model);
        $this->assertStringNotContainsString('committee-dot', $model);

        $loose = $this->render('<x-ui.committee-chip name="Logística" :id="7" icon="truck" />');

        $this->assertStringContainsString(self::TRUCK, $loose);
    }

    public function test_an_unknown_icon_or_the_old_font_awesome_markup_falls_back_to_the_dot_without_printing_it(): void
    {
        foreach ([self::FONT_AWESOME, 'no-existe', '../../etc/passwd', 'Truck', '', null] as $icon) {
            $html = $this->render('<x-ui.committee-chip :committee="$committee" />', ['committee' => $this->committee((string) $icon)]);

            $this->assertStringContainsString('committee-dot hue-30', $html, var_export($icon, true));
            $this->assertStringNotContainsString('<svg', $html, var_export($icon, true));
            $this->assertStringNotContainsString('<i ', $html);
            $this->assertStringNotContainsString('fa-', $html);
            $this->assertStringNotContainsString('&lt;i', $html, 'The stored markup is not even printed escaped');
            $this->assertStringNotContainsString('passwd', $html);
            $this->assertCspSafe($html);
        }
    }

    public function test_with_href_the_chip_is_a_link(): void
    {
        $html = $this->render('<x-ui.committee-chip name="Logística" :id="7" href="/comites/7" />');

        $this->assertMatchesRegularExpression('/^<a href="\/comites\/7"\s+class="inline-flex items-center rounded-full glass-2 font-medium text-ink gap-1.75 py-1 pr-2.5 pl-2 text-sm no-underline transition hover:text-brand-ink pointer-coarse:min-h-11 pointer-coarse:min-w-11 pointer-coarse:justify-center">/', trim($html));
        $this->assertStringEndsWith('</a>', trim($html));
        $this->assertStringContainsString('committee-dot hue-30', $html);
        $this->assertStringNotContainsString('pointer-coarse', $this->render('<x-ui.committee-chip name="Logística" :id="7" />'), 'Only the link grows to 44 px');
    }

    public function test_the_md_size_grows_the_pill_the_dot_and_the_icon(): void
    {
        $dot = $this->render('<x-ui.committee-chip name="Logística" :id="7" size="md" />');

        $this->assertStringContainsString('gap-2 py-1.5 pr-3 pl-2.5 text-md', $dot);
        $this->assertStringContainsString('size-2.5', $dot);
        $this->assertStringNotContainsString('text-sm', $dot);

        $icon = $this->render('<x-ui.committee-chip name="Logística" :id="7" icon="truck" size="md" />');

        $this->assertStringContainsString('class="shrink-0 size-4 text-ink-muted"', $icon);
    }

    public function test_it_needs_a_name_and_escapes_it(): void
    {
        try {
            $this->render('<x-ui.committee-chip :id="7" />');
            $this->fail('A committee chip without a name was rendered');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        }

        $html = $this->render('<x-ui.committee-chip :committee="$committee" />', ['committee' => new Committee(['name' => '<b>Logística</b>', 'icon' => null])]);

        $this->assertStringContainsString('&lt;b&gt;Logística&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringContainsString('committee-dot hue-0', $html, 'An unsaved model has no id');
    }
}
