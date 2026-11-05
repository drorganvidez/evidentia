<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Evidence;
use App\Models\File;
use App\Models\Proof;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileRemovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_cannot_delete_proof_files_by_id(): void
    {
        Storage::fake();
        $owner = User::factory()->create();
        $evidence = Evidence::create([
            'title' => 'Organización de mesas',
            'description' => 'Organicé las mesas del congreso.',
            'hours' => 2,
            'status' => 'ACCEPTED',
            'user_id' => $owner->id,
            'committee_id' => Committee::create(['name' => 'Programa'])->id,
        ]);
        $route = 'proofs/'.$owner->username.'/evidence_'.$evidence->id.'/certificado.pdf';
        Storage::put($route, 'PDF');
        $file = File::create(['name' => 'certificado', 'type' => 'pdf', 'route' => $route, 'size' => 3]);
        $proof = Proof::create(['evidence_id' => $evidence->id, 'file_id' => $file->id]);

        $this->actingAs(User::factory()->create());
        $this->post('/file/remove', ['_id' => $file->id])->assertNotFound();
        $this->post('/proof/remove', ['_id' => $proof->id])->assertNotFound();

        $this->assertModelExists($file);
        $this->assertModelExists($proof);
        Storage::assertExists($route);
    }
}
