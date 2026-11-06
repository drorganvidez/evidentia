<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Evidence;
use App\Models\File;
use App\Models\Proof;

/** The tools of the lecturer and the president: integrity, export, import and the random pick of evidences. */
class CourseToolsScreenTest extends ScreenTestCase
{
    public function test_the_integrity_of_the_evidences_and_of_the_proofs(): void
    {
        $evidence = Evidence::where('title', 'Montaje del photocall')->sole();
        $evidence->forceFill(['stamp' => 'altered'])->save();
        $file = File::forceCreate(['name' => 'acta.pdf', 'type' => 'pdf', 'route' => 'proofs/acta.pdf', 'size' => '10', 'stamp' => 'altered']);
        Proof::forceCreate(['evidence_id' => $evidence->id, 'file_id' => $file->id]);

        foreach (['profesor' => 'lecture', 'presidente' => 'president'] as $who => $prefix) {
            $this->screen($who, route($prefix.'.integrity'))
                ->assertSee('Integridad de las evidencias')
                ->assertSee('Montaje del photocall')
                ->assertSee('Alterada')
                ->assertSee('Evidencias alteradas');

            $this->screen($who, route($prefix.'.integrity', ['ver' => 'pruebas']))
                ->assertSee('Integridad de las pruebas')
                ->assertSee('acta.pdf')
                ->assertSee('aria-current="page"', false);
        }
    }

    public function test_the_export_posts_the_chosen_blocks(): void
    {
        foreach (['profesor' => 'lecture', 'presidente' => 'president'] as $who => $prefix) {
            $html = $this->screen($who, route($prefix.'.export'))
                ->assertSee('action="'.route($prefix.'.export.save').'"', false)
                ->getContent();

            foreach (['evidences', 'meetings', 'events', 'bonus'] as $block) {
                $this->assertMatchesRegularExpression('/name="'.$block.'"[^>]*value="on"[^>]*checked/s', $html);
            }
        }
    }

    public function test_the_import_uploads_the_spreadsheet_first(): void
    {
        $this->screen('profesor', route('lecture.import'))
            ->assertSee('action="'.route('lecture.import.save').'"', false)
            ->assertSee('xls\\/upload\\/process', false)
            ->assertSee('apellidos');
    }

    public function test_the_random_pick_asks_before_replacing_the_previous_one(): void
    {
        $this->screen('profesor', route('randomize.randomize'))
            ->assertSee('action="'.route('randomize.save').'"', false)
            ->assertSee('role="alertdialog"', false)
            ->assertSee('Evidencias elegidas');
    }
}
