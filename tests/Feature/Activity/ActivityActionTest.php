<?php

namespace Tests\Feature\Activity;

use App\Enums\ActivityAction;
use App\Enums\ActivityFlag;
use App\Enums\Deadline;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The closed catalogue of verbs: every verb has a label, a tone and a group, and the noise, the deadlines and the
 * options of the filter come from it.
 */
class ActivityActionTest extends TestCase
{
    private const TONES = ['accepted', 'rejected', 'pending', 'notice', 'draft'];

    public function test_there_are_seventy_verbs_with_a_label_a_tone_and_a_group(): void
    {
        $this->assertCount(70, ActivityAction::cases());

        foreach (ActivityAction::cases() as $action) {
            $this->assertNotSame('', $action->label(), $action->value);
            $this->assertStringNotContainsString(':', $action->label(), $action->value);
            $this->assertContains($action->tone(), self::TONES, $action->value);
            $this->assertArrayHasKey($action->group(), ActivityAction::groups(), $action->value);
            $this->assertLessThanOrEqual(48, strlen($action->value), $action->value);
        }
    }

    public function test_the_labels_are_unique(): void
    {
        $labels = array_map(fn (ActivityAction $action): string => $action->label(), ActivityAction::cases());

        $this->assertSame(count($labels), count(array_unique($labels)));
    }

    public function test_every_group_has_verbs(): void
    {
        $verbs = [];
        foreach (array_keys(ActivityAction::groups()) as $group) {
            $this->assertNotEmpty(ActivityAction::inGroup($group), $group);
            $verbs = [...$verbs, ...ActivityAction::inGroup($group)];
        }

        $this->assertCount(70, $verbs);
        $this->assertSame(
            ['login', 'login_failed', 'logout', 'password_reset_requested', 'password_reset'],
            ActivityAction::inGroup('access'),
        );
        $this->assertSame([], ActivityAction::inGroup('nothing'));
    }

    public function test_the_noise_is_the_accesses_and_the_sent_notices(): void
    {
        $this->assertSame([...ActivityAction::inGroup('access'), 'notification.sent'], ActivityAction::noisy());
        $this->assertTrue(ActivityAction::Login->isNoisy());
        $this->assertTrue(ActivityAction::NotificationSent->isNoisy());
        $this->assertFalse(ActivityAction::NotificationFailed->isNoisy());
        $this->assertFalse(ActivityAction::EvidenceRejected->isNoisy());
    }

    public function test_the_deadline_of_each_verb(): void
    {
        $this->assertSame(Deadline::UploadEvidences, ActivityAction::EvidencePublished->deadline());
        $this->assertSame(Deadline::UploadEvidences, ActivityAction::ProofDeleted->deadline());
        $this->assertSame(Deadline::ValidateEvidences, ActivityAction::EvidenceAccepted->deadline());
        $this->assertSame(Deadline::ValidateEvidences, ActivityAction::EvidenceRejected->deadline());
        $this->assertSame(Deadline::Meetings, ActivityAction::MinutesCreated->deadline());
        $this->assertSame(Deadline::Bonus, ActivityAction::BonusDeleted->deadline());
        $this->assertSame(Deadline::Attendees, ActivityAction::EventbriteAttendeesSynced->deadline());
        // el movimiento recibe su plazo según el estado; convocatorias, hojas y listas no tienen plazo
        $this->assertNull(ActivityAction::EvidenceMoved->deadline());
        $this->assertNull(ActivityAction::RequestCreated->deadline());
        $this->assertNull(ActivityAction::SheetDeleted->deadline());
        $this->assertNull(ActivityAction::DefaultListCreated->deadline());
        $this->assertNull(ActivityAction::UserEdited->deadline());
    }

    public function test_the_options_list_each_group_and_then_its_verbs(): void
    {
        $options = ActivityAction::options();

        $this->assertCount(70 + count(ActivityAction::groups()), $options);
        $this->assertSame(['value' => 'group:access', 'label' => 'Accesos (todos)'], $options[0]);
        $this->assertSame(['value' => 'login', 'label' => 'Inicio de sesión'], $options[1]);
        $this->assertContains(['value' => 'group:evidence', 'label' => 'Evidencias (todos)'], $options);
        $this->assertContains(['value' => 'evidence.moved', 'label' => 'Evidencia movida de comité'], $options);
    }

    public function test_the_flags_have_a_label_and_a_tone(): void
    {
        $this->assertSame('Autorrevisión', ActivityFlag::SelfReview->label());
        $this->assertSame('rejected', ActivityFlag::SelfReview->tone());
        $this->assertSame('Fuera de plazo', ActivityFlag::AfterDeadline->label());
        $this->assertSame('pending', ActivityFlag::AfterDeadline->tone());
        $this->assertSame('Doble papel', ActivityFlag::RoleOverlap->label());
        $this->assertSame('notice', ActivityFlag::RoleOverlap->tone());
    }

    public function test_the_deadlines_name_their_column_and_an_existing_list(): void
    {
        foreach (Deadline::cases() as $deadline) {
            $this->assertSame($deadline->value.'_timestamp', $deadline->column());
            $this->assertTrue(Route::has($deadline->listRoute()), $deadline->listRoute());
            $this->assertNotSame('', $deadline->label());
            $this->assertNotSame('', $deadline->shortLabel());
            $this->assertStringStartsWith('para ', $deadline->verb());
        }
        $this->assertSame('attendee_timestamp', Deadline::Attendees->column());
    }

    public function test_an_annotation_reads_its_verb_and_flags_back_as_enums(): void
    {
        $log = new ActivityLog(['action' => 'evidence.rejected', 'flags' => ['self_review', 'retired', 'after_deadline']]);

        $this->assertSame(ActivityAction::EvidenceRejected, $log->actionEnum());
        $this->assertSame([ActivityFlag::SelfReview, ActivityFlag::AfterDeadline], $log->flagList());
        $this->assertNull((new ActivityLog(['action' => 'retired.verb']))->actionEnum());
        $this->assertSame([], (new ActivityLog(['action' => 'login']))->flagList());
    }
}
