<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Diary;
use App\Models\MeetingRequest;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\SignatureSheet;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MinutesSignatureSheetStepTest extends TestCase
{
    use RefreshDatabase;

    private Secretary $secretary;

    private MeetingRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        $committee = Committee::create(['name' => 'Logística']);
        $this->secretary = Secretary::forceCreate(['user_id' => $user->id, 'committee_id' => $committee->id]);
        $this->request = MeetingRequest::create(['title' => 'Convocatoria de logística', 'place' => 'Aula A3.10',
            'datetime' => '2026-10-16 16:30', 'type' => 1, 'modality' => 1,
            'committee_id' => $committee->id, 'secretary_id' => $this->secretary->id]);
        Diary::create(['meeting_request_id' => $this->request->id]);
        $this->actingAs($user);
    }

    public function test_the_signature_sheet_of_the_chosen_meeting_request_fills_the_attendees(): void
    {
        // Hoja de firmas creada junto a la convocatoria (casilla "crear hoja de firmas")
        $sheet = SignatureSheet::create(['title' => 'Hoja de firmas de la convocatoria', 'random_identifier' => 'ABCD',
            'meeting_request_id' => $this->request->id, 'secretary_id' => $this->secretary->id]);
        $ana = User::factory()->create();
        $sheet->users()->attach($ana);

        $step2 = $this->get(route('secretary.meeting.manage.minutes.create.step2', ['meeting_request' => $this->request->id]))->assertOk();
        $step3 = $this->followingRedirects()->post(route('secretary.meeting.manage.minutes.create.step2_p'), $this->formFields($step2->getContent()));

        $this->assertContains((string) $ana->id, $this->selectedAttendees($step3->getContent()), 'Las firmas de la hoja asociada a la convocatoria no se vuelcan');
    }

    public function test_a_signature_sheet_picked_in_step2_after_a_meeting_request_fills_the_attendees(): void
    {
        $sheet = SignatureSheet::create(['title' => 'Hoja suelta', 'random_identifier' => 'EFGH', 'secretary_id' => $this->secretary->id]);
        $ana = User::factory()->create();
        $sheet->users()->attach($ana);

        $step2 = $this->get(route('secretary.meeting.manage.minutes.create.step2', ['meeting_request' => $this->request->id]))->assertOk();
        $fields = $this->formFields($step2->getContent(), pick: (string) $sheet->id);
        $step3 = $this->followingRedirects()->post(route('secretary.meeting.manage.minutes.create.step2_p'), $fields);

        $this->assertContains((string) $ana->id, $this->selectedAttendees($step3->getContent()), 'La hoja elegida en el paso 2 se ignora');
    }

    /** Campos que enviaría el navegador; $pick elige esa opción en los desplegables que la tengan. */
    private function formFields(string $html, ?string $pick = null): array
    {
        $xpath = $this->xpath($html);
        $fields = [];
        foreach ($xpath->query('//form[contains(@action, "step2_p")]//input[@name]') as $input) {
            $fields[$input->getAttribute('name')] = $input->getAttribute('value');
        }
        foreach ($xpath->query('//form[contains(@action, "step2_p")]//select[@name]') as $select) {
            $values = [];
            foreach ($xpath->query('.//option', $select) as $option) {
                $values[] = $option->getAttribute('value');
            }
            $fields[$select->getAttribute('name')] = in_array($pick, $values, true) ? $pick : ($values[0] ?? '');
        }

        return $fields;
    }

    private function selectedAttendees(string $html): array
    {
        $ids = [];
        foreach ($this->xpath($html)->query('//select[@id="users"]/option[@selected]') as $option) {
            $ids[] = $option->getAttribute('value');
        }

        return $ids;
    }

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);

        return new DOMXPath($dom);
    }
}
