<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Models\ReasonRejection;
use App\Models\Role;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RejectedEvidenceActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_list_confirms_each_action_with_its_own_form(): void
    {
        $this->rejectedEvidence();

        $html = $this->get(route('evidence.list'))->assertOk()->getContent();

        $this->assertEqualsCanonicalizing(
            [route('evidence.reedit'), route('evidence.remove')],
            $this->confirmedActions($html)
        );
    }

    private function rejectedEvidence(): Evidence
    {
        Configuration::forceCreate([
            'upload_evidences_timestamp' => now()->addWeek(),
            'validate_evidences_timestamp' => now()->addWeeks(2),
        ]);
        $student = User::factory()->create();
        $student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
        $this->actingAs($student);

        $evidence = Evidence::create([
            'title' => 'Pokemon',
            'description' => 'Planeando qué pokemons banear.',
            'hours' => 1.75,
            'status' => 'REJECTED',
            'user_id' => $student->id,
            'committee_id' => Committee::create(['name' => 'Programa'])->id,
        ]);
        ReasonRejection::create([
            'reason' => 'Necesito que me mandes un documento con el trabajo realizado',
            'evidence_id' => $evidence->id,
        ]);

        return $evidence;
    }

    /**
     * Action of the form each confirmation modal really submits, resolved the way the browser
     * does it: the first form in the page with the id named in the confirm button.
     */
    private function confirmedActions(string $html): array
    {
        $document = new DOMDocument;
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8"?>'.$html);
        libxml_clear_errors();
        $xpath = new DOMXPath($document);

        $actions = [];
        foreach ($xpath->query('//div[starts-with(@id, "modal-confirm-")]//button[contains(@onclick, "getElementById")]') as $button) {
            preg_match("/getElementById\('([^']+)'\)/", $button->getAttribute('onclick'), $match);
            $form = $xpath->query('//form[@id="'.$match[1].'"]')->item(0);
            $actions[] = $form?->getAttribute('action');
        }

        return $actions;
    }
}
