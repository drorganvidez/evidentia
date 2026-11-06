<?php

namespace Tests\Feature\Screens\Meetings;

use App\Models\Configuration;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\SignatureSheet;
use App\Models\User;

class MinutesScreensTest extends MeetingScreenTestCase
{
    public function test_the_list_opens_the_edition_while_the_deadline_is_open(): void
    {
        $minutes = MeetingMinutes::sole();

        $html = $this->assertScreen($this->as('secretario')->get(route('secretary.meeting.manage.minutes.list')));

        $this->assertStringContainsString('Reunión de logística', $html);
        $this->assertStringContainsString(route('secretary.meeting.manage.minutes.edit', ['id' => $minutes->id]), $html);
        $this->assertStringContainsString(route('download.minutes', ['id' => $minutes->id]), $html);
        $this->assertStringContainsString('action="'.route('secretary.meeting.manage.minutes.remove').'"', $html);
    }

    public function test_the_list_only_offers_the_pdf_after_the_deadline(): void
    {
        Configuration::query()->update(['meetings_timestamp' => now()->subDay()]);
        $minutes = MeetingMinutes::sole();

        $html = $this->assertScreen($this->as('secretario')->get(route('secretary.meeting.manage.minutes.list')));

        $this->assertStringContainsString(route('download.minutes', ['id' => $minutes->id]), $html);
        $this->assertStringNotContainsString(route('secretary.meeting.manage.minutes.edit', ['id' => $minutes->id]), $html);
        $this->assertStringNotContainsString(route('secretary.meeting.manage.minutes.remove'), $html);
    }

    public function test_the_three_steps_carry_the_call_and_the_sheet(): void
    {
        $request = MeetingRequest::sole();
        $sheet = SignatureSheet::sole();

        $step1 = $this->assertScreen($this->as('secretario')->get(route('secretary.meeting.manage.minutes.create.step1')));
        $this->assertStringContainsString('aria-label="Pasos del acta"', $step1);
        $this->assertStringContainsString('name="meeting_request"', $step1);

        $step2 = $this->assertScreen($this->get(route('secretary.meeting.manage.minutes.create.step2', ['meeting_request' => $request->id])));
        $this->assertStringContainsString('name="signature_sheet" value="'.$sheet->id.'"', $step2);
        $this->assertStringContainsString('Hoja de firmas de logística', $step2);

        $step3 = $this->assertScreen($this->get(route('secretary.meeting.manage.minutes.create.step3', [
            'meeting_request' => $request->id, 'signature_sheet' => $sheet->id,
        ])));
        $this->assertStringContainsString('x-data="minutesEditor"', $step3);
        $this->assertStringContainsString('x-data="picker"', $step3);
        $this->assertStringContainsString('Equipo de montaje', $step3);
        $this->assertStringContainsString('value="Convocatoria de logística"', $step3);
        $this->assertStringContainsString('Reparto de tareas', $step3);
        $this->assertMatchesRegularExpression('/<option value="'.User::where('email', 'estudiante@e2e.test')->value('id').'" selected/', $step3);
    }

    public function test_the_edition_loads_the_meeting_its_attendees_and_its_points(): void
    {
        $minutes = MeetingMinutes::sole();

        $html = $this->assertScreen($this->as('secretario')->get(route('secretary.meeting.manage.minutes.edit', ['id' => $minutes->id])));

        $this->assertStringContainsString('name="_id" value="'.$minutes->id.'"', $html);
        $this->assertStringContainsString('value="Reunión de logística"', $html);
        $this->assertStringContainsString('name="points_json"', $html);
        $this->assertStringContainsString('Cada equipo nombra un responsable.', $html);
        $this->assertMatchesRegularExpression('/name="hours"[^>]*value="2"/s', $html);
        $this->assertStringContainsString(route('download.minutes', ['id' => $minutes->id]), $html);
    }
}
