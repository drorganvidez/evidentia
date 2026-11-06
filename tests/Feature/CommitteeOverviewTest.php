<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Meeting;
use App\Models\MeetingRequest;
use App\Models\MessageThread;
use App\Models\User;
use App\View\CommitteeOverview;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The signals of a committee for the management over the seed of the end-to-end tests: Logística works (no signal),
 * Comunicación has nobody in charge and never met, Presidencia is coordinated by the president; each problem names
 * who to nudge and prefills the message.
 */
class CommitteeOverviewTest extends TestCase
{
    use RefreshDatabase;

    private Committee $logistics;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(E2eSeeder::class);
        $this->logistics = Committee::where('name', 'Logística')->sole();
        $this->actingAs(User::where('username', 'profesor')->sole());
    }

    private function signals(Committee $committee): array
    {
        return collect(app(CommitteeOverview::class)->signals($committee))->keyBy('key')->all();
    }

    public function test_a_committee_that_works_has_no_signal(): void
    {
        $this->assertSame([], $this->signals($this->logistics));
    }

    public function test_a_committee_without_people_in_charge_nor_meetings(): void
    {
        $signals = $this->signals(Committee::where('name', 'Comunicación')->sole());

        $this->assertSame(['no-coordinator', 'no-secretary', 'no-meetings'], array_keys($signals));
        $this->assertSame(route('lecture.user.list'), $signals['no-coordinator']['action']['href']);
        $this->assertSame('Sin reuniones todavía', $signals['no-meetings']['title']);
        // nobody to write to
        $this->assertNull($signals['no-meetings']['action']);
    }

    public function test_late_evidences_nudge_the_coordination(): void
    {
        DB::table('evidences')->where('title', 'Atención en el registro')->update(['updated_at' => now()->subDays(10)]);

        $signal = $this->signals($this->logistics)['late-evidences'];

        $this->assertSame('rejected', $signal['tone']);
        $this->assertSame('1 evidencia espera más de 7 días', $signal['title']);
        $this->assertSame('La más antigua lleva 10 días en la cola', $signal['detail']);
        $this->assertStringStartsWith(route('messages.create').'?', $signal['action']['href']);
        $this->assertStringContainsString('to=COORDINATOR%3A'.$this->logistics->id, $signal['action']['href']);
        $this->assertStringContainsString('subject=Evidencias%20pendientes%20de%20revisar%20en%20Log', $signal['action']['href']);
    }

    public function test_late_evidences_of_its_coordinators_point_to_the_queue_of_the_presidency(): void
    {
        DB::table('evidences')->where('title', 'Inventario del almacén')->update(['updated_at' => now()->subDays(9)]);

        $signals = $this->signals($this->logistics);

        $this->assertArrayNotHasKey('late-evidences', $signals);
        $this->assertSame('1 evidencia de la coordinación espera más de 7 días', $signals['late-presidency']['title']);
        $this->assertSame('La más antigua lleva 9 días en la cola de presidencia', $signals['late-presidency']['detail']);
        $this->assertSame(route('president.review.pending'), $signals['late-presidency']['action']['href']);
    }

    public function test_the_presidents_coordinate_the_committee_of_the_presidency(): void
    {
        $presidency = Committee::presidency();
        DB::table('evidences')->where('title', 'Acreditación de ponentes')->update(['updated_at' => now()->subDays(8)]);

        $signals = $this->signals($presidency);

        $this->assertArrayNotHasKey('no-coordinator', $signals);
        $this->assertArrayNotHasKey('late-presidency', $signals);
        $this->assertSame('1 evidencia espera más de 7 días', $signals['late-evidences']['title']);
        $this->assertSame(route('president.review.pending'), $signals['late-evidences']['action']['href']);
        $this->assertSame(['Elena'], app(CommitteeOverview::class)->for($presidency, User::where('username', 'profesor')->sole())['presidents']->pluck('name')->all());

        User::where('username', 'presidente')->sole()->roles()->detach();
        $this->assertArrayHasKey('no-coordinator', $this->signals($presidency));
    }

    public function test_a_meeting_request_held_without_minutes_nudges_the_secretary_about_it(): void
    {
        $request = MeetingRequest::sole();
        $request->update(['datetime' => now()->subDays(4)]);

        $signal = $this->signals($this->logistics)['requests-without-minutes'];

        $this->assertSame('1 convocatoria celebrada sin acta', $signal['title']);
        $this->assertStringContainsString('request='.$request->id, $signal['action']['href']);
        $this->assertStringContainsString('to=SECRETARY%3A'.$this->logistics->id, $signal['action']['href']);
        $this->assertSame('Escribir a la secretaría', $signal['action']['label']);

        $request->update(['meeting_id' => Meeting::sole()->id]);
        $this->assertArrayNotHasKey('requests-without-minutes', $this->signals($this->logistics));
    }

    public function test_messages_without_an_answer_for_days(): void
    {
        MessageThread::where('subject', 'Cambio de turno en el registro')->update(['last_message_at' => now()->subDays(5)]);

        $signal = $this->signals($this->logistics)['unanswered-messages'];

        $this->assertSame('1 mensaje sin responder', $signal['title']);
        $this->assertStringContainsString('«Cambio de turno en el registro»', $signal['detail']);

        // the answered one does not count however old it is
        MessageThread::where('subject', 'Duda sobre el photocall')->update(['last_message_at' => now()->subDays(9)]);
        $this->assertSame('1 mensaje sin responder', $this->signals($this->logistics)['unanswered-messages']['title']);
    }

    public function test_a_quiet_committee_and_rejected_evidences_nobody_corrected(): void
    {
        Meeting::sole()->update(['datetime' => now()->subDays(30)]);
        DB::table('evidences')->where('title', 'Recogida de material')->update(['updated_at' => now()->subDays(9)]);

        $signals = $this->signals($this->logistics);

        $this->assertSame('Sin reuniones desde hace 30 días', $signals['no-meetings']['title']);
        $this->assertStringContainsString('to=SECRETARY%3A', $signals['no-meetings']['action']['href']);
        $this->assertSame('1 evidencia rechazada sin corregir', $signals['rejected-uncorrected']['title']);
        $this->assertSame(
            route('lecture.evidence.list', ['estado' => 'rechazadas', 'comite' => $this->logistics->id]),
            $signals['rejected-uncorrected']['action']['href'],
        );
    }

    public function test_the_summaries_of_every_committee(): void
    {
        $summaries = app(CommitteeOverview::class)->summaries()->keyBy(fn (array $summary) => $summary['committee']->name);

        $this->assertSame(['Comunicación', 'Logística', 'Presidencia'], $summaries->keys()->all());
        // the evidence of Inés waits in the presidency, not in the queue of Logística
        $this->assertSame(2, $summaries['Logística']['pending']);
        $this->assertSame(5, $summaries['Logística']['sent']);
        $this->assertSame(1, $summaries['Logística']['meetings']);
        // 2 h accepted, 2 h × 2 at the meeting and the 3 h bonus
        $this->assertSame(9.0, $summaries['Logística']['hours']);
        $this->assertSame([], $summaries['Logística']['signals']);
        $this->assertCount(3, $summaries['Comunicación']['signals']);
        $this->assertSame(0.0, $summaries['Comunicación']['hours']);
        $this->assertSame(['Elena'], $summaries['Presidencia']['presidents']->pluck('name')->all());
        $this->assertSame([], $summaries['Logística']['presidents']->all());
        $this->assertSame(['no-secretary', 'no-meetings'], array_column($summaries['Presidencia']['signals'], 'key'));
    }
}
