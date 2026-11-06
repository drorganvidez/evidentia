<?php

namespace Tests\Feature\Screens\Meetings;

use App\Models\SignatureSheet;

class SignatureSheetScreensTest extends MeetingScreenTestCase
{
    public function test_the_list_shows_each_sheet_with_its_link_to_sign(): void
    {
        $sheet = SignatureSheet::sole();

        $html = $this->assertScreen($this->as('secretario')->get(route('secretary.meeting.manage.signaturesheet.list')));

        $this->assertStringContainsString('Hoja de firmas de logística', $html);
        $this->assertStringContainsString(url('/sign/7391'), $html);
        $this->assertStringContainsString(route('secretary.meeting.manage.signaturesheet.view', ['id' => $sheet->id]), $html);
        $this->assertStringContainsString('action="'.route('secretary.meeting.manage.signaturesheet.remove').'"', $html);
    }

    public function test_the_sheet_shows_who_signed_and_its_call(): void
    {
        $sheet = SignatureSheet::sole();

        $html = $this->assertScreen($this->as('secretario')->get(route('secretary.meeting.manage.signaturesheet.view', ['id' => $sheet->id])));

        $this->assertStringContainsString('Lucía', $html);
        $this->assertStringContainsString('estudiante', $html);
        $this->assertStringContainsString('Convocatoria de logística', $html);
        $this->assertStringContainsString('data-copy="'.url('/sign/7391').'"', $html);
    }

    public function test_the_forms_keep_their_field_names(): void
    {
        $sheet = SignatureSheet::sole();

        $create = $this->assertScreen($this->as('secretario')->get(route('secretary.meeting.manage.signaturesheet.create')));
        $this->assertStringContainsString('name="meeting_request"', $create);
        $this->assertMatchesRegularExpression('/<option value=""[^>]*>\s*Sin convocatoria\s*<\/option>/', $create);

        $edit = $this->assertScreen($this->get(route('secretary.meeting.manage.signaturesheet.edit', ['id' => $sheet->id])));
        $this->assertStringContainsString('name="meeting_request_id"', $edit);
        $this->assertStringContainsString('name="_id" value="'.$sheet->id.'"', $edit);
        $this->assertStringContainsString('value="Hoja de firmas de logística"', $edit);
    }
}
