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

class SignatureSheetPerRequestTest extends TestCase
{
    use RefreshDatabase;

    private Secretary $secretary;

    private MeetingRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Configuration::forceCreate(['meetings_timestamp' => now()->addMonth()]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        $committee = Committee::create(['name' => 'Logística']);
        $this->secretary = Secretary::forceCreate(['user_id' => $user->id, 'committee_id' => $committee->id]);
        $this->request = MeetingRequest::forceCreate([
            'title' => 'Reunión de logística', 'committee_id' => $committee->id, 'secretary_id' => $this->secretary->id,
        ]);
        SignatureSheet::create([
            'title' => 'Hoja de la convocatoria', 'random_identifier' => '1111',
            'meeting_request_id' => $this->request->id, 'secretary_id' => $this->secretary->id,
        ]);
        $this->actingAs($user);
    }

    public function test_requests_with_a_sheet_are_not_offered_again(): void
    {
        $this->get(route('secretary.meeting.manage.signaturesheet.create'))
            ->assertOk()
            ->assertViewHas('available_meeting_requests', fn ($requests) => ! $requests->contains('id', $this->request->id));
    }

    public function test_a_request_keeps_a_single_signature_sheet(): void
    {
        $this->post(route('secretary.meeting.manage.signaturesheet.new'), [
            'title' => 'Segunda hoja', 'meeting_request' => $this->request->id,
        ])->assertSessionHasErrors('meeting_request');

        $other = SignatureSheet::create(['title' => 'Hoja suelta', 'random_identifier' => '2222', 'secretary_id' => $this->secretary->id]);
        $this->post(route('secretary.meeting.manage.signaturesheet.save'), [
            '_id' => $other->id, 'title' => 'Hoja suelta', 'meeting_request_id' => $this->request->id,
        ]);

        $this->assertSame(1, SignatureSheet::where('meeting_request_id', $this->request->id)->count());
    }
}
