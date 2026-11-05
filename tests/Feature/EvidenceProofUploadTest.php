<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Models\Proof;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvidenceProofUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private Committee $committee;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Configuration::forceCreate(['upload_evidences_timestamp' => now()->addWeek(), 'secret' => 'salt']);
        $this->committee = Committee::create(['name' => 'Programa']);
        $this->student = User::factory()->create(['username' => 'anagar']);
        $this->student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
        $this->actingAs($this->student)->withSession(['_token' => 'upload-session']);
    }

    public function test_an_uploaded_file_can_be_reverted(): void
    {
        $serverId = $this->upload('certificado.pdf')->assertOk()->getContent();

        Storage::assertExists('tmp/anagar/upload-session/certificado.pdf');

        $this->call('DELETE', route('upload.revert'), [], [], [], [], $serverId)->assertOk();
        Storage::assertMissing('tmp/anagar/upload-session/certificado.pdf');
    }

    public function test_an_upload_without_a_file_is_rejected(): void
    {
        $this->post(route('upload.process'))->assertStatus(400);
    }

    public function test_a_published_evidence_keeps_its_uploaded_proofs(): void
    {
        $this->upload('certificado.pdf');

        $this->post(route('evidence.publish'), $this->form())->assertRedirect(route('evidence.list'));

        $evidence = Evidence::sole();
        $proof = Proof::sole();
        $this->assertSame($evidence->id, $proof->evidence_id);
        $this->assertSame('proofs/anagar/evidence_'.$evidence->id.'/certificado.pdf', $proof->file->route);
        Storage::assertExists($proof->file->route);
        Storage::assertMissing('tmp/anagar/upload-session/certificado.pdf');
        $this->assertTrue($proof->integrity());
    }

    public function test_editing_an_evidence_carries_its_proofs_to_the_new_version(): void
    {
        $this->upload('certificado.pdf');
        $this->post(route('evidence.draft'), $this->form());
        $draft = Evidence::sole();

        // opening the editor copies the proofs back to the temporary folder of the session
        $this->get(route('evidence.edit', ['id' => $draft->id]))->assertOk();
        $token = session()->token();
        Storage::assertExists('tmp/anagar/'.$token.'/certificado.pdf');

        $this->withSession(['_token' => $token])->post(route('evidence.publish.edit'), ['_id' => $draft->id] + $this->form());

        $published = Evidence::where('last', true)->sole();
        $this->assertSame('PENDING', $published->status);
        $this->assertSame(['certificado'], $published->proofs->map(fn ($proof) => $proof->file->name)->all());
        Storage::assertExists($published->proofs->first()->file->route);
    }

    public function test_the_import_uploads_its_spreadsheet_the_same_way(): void
    {
        $this->post(route('xls.upload.process'), ['files' => [UploadedFile::fake()->create('alumnos.xlsx', 10)]])->assertOk();

        Storage::assertExists('tmp/anagar/upload-session/alumnos.xlsx');
    }

    private function upload(string $name)
    {
        return $this->post(route('upload.process'), ['files' => [UploadedFile::fake()->create($name, 20, 'application/pdf')]]);
    }

    private function form(): array
    {
        return [
            'title' => 'Organización de mesas',
            'hours' => 2,
            'minutes' => 0,
            'committee' => $this->committee->id,
            'description' => 'Organicé las mesas del congreso durante dos horas.',
        ];
    }
}
