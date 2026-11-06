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

    public function test_the_detail_page_lets_the_student_edit_a_rejected_evidence_again(): void
    {
        $evidence = $this->rejectedEvidence();

        $html = $this->get(route('evidence.view', ['id' => $evidence->id]))->assertOk()->getContent();

        $this->assertEqualsCanonicalizing(
            [route('evidence.reedit'), route('evidence.remove')],
            $this->confirmedActions($html)
        );
    }

    public function test_a_re_edited_evidence_opens_in_the_editor_with_its_rejection_reason(): void
    {
        $evidence = $this->rejectedEvidence();

        $this->post(route('evidence.reedit'), ['_id' => $evidence->id])
            ->assertRedirect(route('evidence.list'));

        $this->assertSame('DRAFT', $evidence->fresh()->status);
        $this->get(route('evidence.edit', ['id' => $evidence->id]))
            ->assertOk()
            ->assertSee('Necesito que me mandes un documento con el trabajo realizado');
    }

    public function test_only_rejected_evidences_go_back_to_draft(): void
    {
        $evidence = $this->rejectedEvidence();

        foreach (['PENDING', 'ACCEPTED'] as $status) {
            $evidence->update(['status' => $status]);
            $this->post(route('evidence.reedit'), ['_id' => $evidence->id])->assertNotFound();
            $this->assertSame($status, $evidence->fresh()->status);
        }
    }

    public function test_rejected_evidences_cannot_be_edited_again_after_the_upload_deadline(): void
    {
        $evidence = $this->rejectedEvidence();
        Configuration::find(1)->update(['upload_evidences_timestamp' => now()->subDay()]);

        $html = $this->get(route('evidence.view', ['id' => $evidence->id]))->assertOk()->getContent();

        $this->assertSame([], $this->confirmedActions($html));
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
     * Action of the form each confirmation dialog really submits: the form inside every alert dialog of the page.
     */
    private function confirmedActions(string $html): array
    {
        $document = new DOMDocument;
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8"?>'.$html);
        libxml_clear_errors();
        $xpath = new DOMXPath($document);

        $actions = [];
        foreach ($xpath->query('//dialog[@role="alertdialog"]//form') as $form) {
            $actions[] = $form->getAttribute('action');
        }

        return $actions;
    }
}
