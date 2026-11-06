<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Coordinator;
use App\Models\Evidence;
use App\Models\File;
use App\Models\Proof;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProofPreviewTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private Committee $committee;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Configuration::forceCreate([
            'upload_evidences_timestamp' => now()->addWeek(),
            'validate_evidences_timestamp' => now()->addWeeks(2),
        ]);
        $this->committee = Committee::create(['name' => 'Programa']);
        $this->student = User::factory()->create();
        $this->student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
    }

    public function test_a_pdf_opens_inline_with_its_own_type_and_a_closed_policy(): void
    {
        $proof = $this->proof('PENDING', 'acta', 'pdf', '%PDF-1.4');

        $response = $this->actingAs($this->student)->get(route('proof.view', ['id' => $proof->id]));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('inline', $response->headers->get('Content-Disposition'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertStringContainsString("default-src 'none'", $response->headers->get('Content-Security-Policy'));
    }

    public function test_an_image_opens_inline_in_a_sandbox(): void
    {
        $proof = $this->proof('PENDING', 'cartel', 'JPG', 'jpeg-bytes');

        $response = $this->actingAs($this->student)->get(route('proof.view', ['id' => $proof->id]));

        $response->assertOk();
        $this->assertSame('image/jpeg', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('inline', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('sandbox', $response->headers->get('Content-Security-Policy'));
    }

    public function test_any_other_file_goes_to_its_download(): void
    {
        foreach (['zip', 'svg', 'docx', 'html'] as $type) {
            $proof = $this->proof('PENDING', 'archivo', $type, 'bytes');

            $this->actingAs($this->student)
                ->get(route('proof.view', ['id' => $proof->id]))
                ->assertRedirect(route('proof.download', ['id' => $proof->id]));
        }
    }

    public function test_the_preview_keeps_the_rules_of_the_download(): void
    {
        $draft = $this->proof('DRAFT', 'acta', 'pdf', '%PDF-1.4');
        $other = User::factory()->create();
        $other->roles()->attach(Role::where('rol', 'STUDENT')->first());
        $coordinator = User::factory()->create();
        $coordinator->roles()->attach(Role::forceCreate(['rol' => 'COORDINATOR', 'slug' => 'coordinator']));
        Coordinator::forceCreate(['user_id' => $coordinator->id, 'committee_id' => $this->committee->id]);

        $this->actingAs($other)->get(route('proof.view', ['id' => $draft->id]))->assertNotFound();
        $this->actingAs($coordinator)->get(route('proof.view', ['id' => $draft->id]))->assertNotFound();
        auth()->logout();
        $this->get(route('proof.view', ['id' => $draft->id]))->assertRedirect();
    }

    public function test_the_evidence_offers_to_see_only_what_the_browser_shows(): void
    {
        $pdf = $this->proof('PENDING', 'acta', 'pdf', '%PDF-1.4');
        Proof::create(['evidence_id' => $pdf->evidence_id, 'file_id' => File::create(['name' => 'codigo', 'type' => 'zip', 'route' => 'proofs/codigo.zip', 'size' => 5])->id]);

        $this->actingAs($this->student)
            ->get(route('evidence.view', ['id' => $pdf->evidence_id]))
            ->assertOk()
            ->assertSee('aria-label="Ver acta.pdf"', false)
            ->assertSee(route('proof.view', ['id' => $pdf->id]), false)
            ->assertDontSee('aria-label="Ver codigo.zip"', false)
            ->assertSee('aria-label="Descargar codigo.zip"', false);
    }

    private function proof(string $status, string $name, string $type, string $content): Proof
    {
        $evidence = Evidence::create([
            'title' => 'Organización de mesas',
            'description' => 'Organicé las mesas del congreso durante dos horas.',
            'hours' => 2,
            'status' => $status,
            'user_id' => $this->student->id,
            'committee_id' => $this->committee->id,
        ]);
        $route = 'proofs/'.$name.'-'.$evidence->id.'.'.strtolower($type);
        Storage::put($route, $content);
        $file = File::create(['name' => $name, 'type' => $type, 'route' => $route, 'size' => strlen($content)]);

        return Proof::create(['evidence_id' => $evidence->id, 'file_id' => $file->id]);
    }
}
