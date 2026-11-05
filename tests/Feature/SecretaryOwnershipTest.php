<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\MeetingRequest;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\SignatureSheet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecretaryOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Configuration::forceCreate(['meetings_timestamp' => now()->addMonth(), 'bonus_timestamp' => now()->addMonth()]);
        $this->role = Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']);
    }

    public function test_a_secretary_removes_their_own_meeting_request(): void
    {
        $secretary = $this->secretary();
        $request = $this->requestOf($secretary);

        $this->actingAs($secretary->user)
            ->post(route('secretary.meeting.manage.request.remove'), ['_id' => $request->id])
            ->assertRedirect(route('secretary.meeting.manage.request.list'));

        $this->assertModelMissing($request);
    }

    public function test_a_secretary_cannot_remove_another_committees_meeting_request_by_sending_their_own_id(): void
    {
        $foreign = $this->requestOf($this->secretary());
        $intruder = $this->secretary();

        $this->actingAs($intruder->user)->post(route('secretary.meeting.manage.request.remove'), [
            '_id' => $this->requestOf($intruder)->id,
            'meeting_request_id' => $foreign->id,
        ]);

        $this->assertModelExists($foreign);
    }

    public function test_a_secretary_removes_their_own_signature_sheet(): void
    {
        $secretary = $this->secretary();
        $sheet = $this->sheetOf($secretary);

        $this->actingAs($secretary->user)
            ->post(route('secretary.meeting.manage.signaturesheet.remove'), ['_id' => $sheet->id])
            ->assertRedirect(route('secretary.meeting.manage.signaturesheet.list'));

        $this->assertModelMissing($sheet);
    }

    public function test_a_secretary_cannot_remove_another_committees_signature_sheet_by_sending_their_own_id(): void
    {
        $foreign = $this->sheetOf($this->secretary());
        $intruder = $this->secretary();

        $this->actingAs($intruder->user)->post(route('secretary.meeting.manage.signaturesheet.remove'), [
            '_id' => $this->sheetOf($intruder)->id,
            'signature_sheet_id' => $foreign->id,
        ]);

        $this->assertModelExists($foreign);
    }

    public function test_a_secretary_views_the_signers_of_their_own_signature_sheet(): void
    {
        $secretary = $this->secretary();
        $sheet = $this->sheetOf($secretary);
        $sheet->users()->attach(User::factory()->create(['name' => 'Firmante']));

        $this->actingAs($secretary->user)
            ->get(route('secretary.meeting.manage.signaturesheet.view', ['id' => $sheet->id]))
            ->assertOk()
            ->assertSee('Firmante');
    }

    public function test_a_secretary_cannot_view_another_committees_signature_sheet(): void
    {
        $foreign = $this->sheetOf($this->secretary());

        $this->actingAs($this->secretary()->user)
            ->get(route('secretary.meeting.manage.signaturesheet.view', ['id' => $foreign->id]))
            ->assertNotFound();
    }

    public function test_users_without_a_secretary_profile_get_not_found_on_requests_and_sheets(): void
    {
        $secretary = $this->secretary();
        $request = $this->requestOf($secretary);
        $orphanSheet = SignatureSheet::create(['title' => 'Hoja huérfana', 'random_identifier' => '4321', 'secretary_id' => null]);
        $student = User::factory()->create();
        $student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
        $this->actingAs($student);

        $this->get(route('secretary.meeting.manage.request.edit', $request->id))->assertNotFound();
        $this->get(route('secretary.meeting.manage.signaturesheet.edit', $orphanSheet->id))->assertNotFound();
    }

    private function secretary(): Secretary
    {
        $user = User::factory()->create();
        $user->roles()->attach($this->role);

        return Secretary::forceCreate([
            'user_id' => $user->id,
            'committee_id' => Committee::create(['name' => 'Committee '.$user->id])->id,
        ]);
    }

    private function requestOf(Secretary $secretary): MeetingRequest
    {
        return MeetingRequest::forceCreate([
            'title' => 'Convocatoria '.$secretary->id,
            'place' => 'Aula A3.10',
            'datetime' => now()->addWeek(),
            'type' => '1',
            'modality' => '1',
            'committee_id' => $secretary->committee_id,
            'secretary_id' => $secretary->id,
        ]);
    }

    private function sheetOf(Secretary $secretary): SignatureSheet
    {
        return SignatureSheet::create([
            'title' => 'Hoja '.$secretary->id,
            'random_identifier' => (string) (1000 + $secretary->id),
            'secretary_id' => $secretary->id,
        ]);
    }
}
