<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Models\File;
use App\Models\Proof;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProofIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private Proof $proof;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Configuration::forceCreate(['secret' => 'salt']);
        $student = User::factory()->create();
        $evidence = Evidence::create([
            'title' => 'Organización de mesas',
            'description' => 'Organicé las mesas del congreso.',
            'hours' => 2,
            'status' => 'PENDING',
            'user_id' => $student->id,
            'committee_id' => Committee::create(['name' => 'Programa'])->id,
        ]);
        Storage::disk('local')->put('proofs/ana/evidence_1/certificado.pdf', '%PDF-1.4');
        $file = File::create(['name' => 'certificado', 'type' => 'pdf', 'route' => 'proofs/ana/evidence_1/certificado.pdf', 'size' => 8]);
        \Stamp::compute_file($file)->save();
        $this->proof = Proof::create(['evidence_id' => $evidence->id, 'file_id' => $file->id]);
    }

    public function test_an_untouched_proof_passes_the_integrity_check(): void
    {
        $this->assertTrue($this->proof->integrity());

        $lecture = User::factory()->create();
        $lecture->roles()->attach(Role::forceCreate(['rol' => 'LECTURE', 'slug' => 'lecture']));
        $this->actingAs($lecture)->get(route('lecture.integrity'))->assertOk();
    }

    public function test_a_changed_or_missing_proof_fails_the_integrity_check(): void
    {
        Storage::disk('local')->put('proofs/ana/evidence_1/certificado.pdf', '%PDF-1.4 cambiado');
        $this->assertFalse($this->proof->fresh()->integrity());

        Storage::disk('local')->delete('proofs/ana/evidence_1/certificado.pdf');
        $this->assertFalse($this->proof->fresh()->integrity());
    }
}
