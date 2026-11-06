<?php

namespace Tests\Feature;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Models\ReasonRejection;
use App\Models\Role;
use App\Models\User;
use App\Services\EvidenceReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class EvidenceReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private User $coordinator;

    private Evidence $evidence;

    protected function setUp(): void
    {
        parent::setUp();
        // the notices these actions send are checked in tests/Feature/Notifications
        Notification::fake();
        Configuration::forceCreate(['id' => 1, 'validate_evidences_timestamp' => now()->addWeek()]);
        $committee = Committee::create(['name' => 'Programa']);
        $this->student = User::factory()->create();
        $this->coordinator = User::factory()->create();
        $this->coordinator->roles()->attach(Role::forceCreate(['rol' => 'COORDINATOR', 'slug' => 'coordinator']));
        $this->evidence = Evidence::create(['title' => 'Mesas', 'hours' => 2, 'status' => 'PENDING', 'user_id' => $this->student->id, 'committee_id' => $committee->id]);
        $this->actingAs($this->coordinator);
    }

    public function test_accepting_changes_the_status_and_annotates_it(): void
    {
        app(EvidenceReview::class)->accept($this->evidence, 'COORDINATOR');

        $this->assertSame('ACCEPTED', $this->evidence->fresh()->status);
        $log = ActivityLog::sole();
        $this->assertSame('evidence.accepted', $log->action);
        $this->assertSame('COORDINATOR', $log->acting_role);
        $this->assertSame($this->student->id, $log->target_user_id);
        $this->assertEquals(['before' => 'PENDING', 'after' => 'ACCEPTED'], $log->details);
        $this->assertNull($log->flags);
    }

    public function test_rejecting_stores_the_reason_like_today_and_annotates_it(): void
    {
        app(EvidenceReview::class)->reject($this->evidence, 'Falta el certificado.', 'COORDINATOR');

        $this->assertSame('REJECTED', $this->evidence->fresh()->status);
        $rejection = ReasonRejection::sole();
        $this->assertSame('Falta el certificado.', $rejection->reason);
        $this->assertSame($this->evidence->id, $rejection->evidence_id);
        $log = ActivityLog::sole();
        $this->assertSame('evidence.rejected', $log->action);
        $this->assertEquals(
            ['before' => 'PENDING', 'after' => 'REJECTED', 'reason' => 'Falta el certificado.', 'reason_rejection_id' => $rejection->id],
            $log->details,
        );
    }

    public function test_a_review_after_the_validation_deadline_is_flagged(): void
    {
        Configuration::first()->update(['validate_evidences_timestamp' => now()->subDay()]);
        $lecturer = User::factory()->create();
        $lecturer->roles()->attach(Role::forceCreate(['rol' => 'LECTURE', 'slug' => 'lecture']));
        $this->actingAs($lecturer);

        app(EvidenceReview::class)->accept($this->evidence, 'LECTURE');

        $this->assertSame(['after_deadline'], ActivityLog::sole()->flags);
    }

    public function test_a_failed_annotation_leaves_the_evidence_untouched(): void
    {
        Activity::fake()->failOn(ActivityAction::EvidenceRejected);

        try {
            app(EvidenceReview::class)->reject($this->evidence, 'Falta el certificado.');
        } catch (RuntimeException) {
        }

        $this->assertSame('PENDING', $this->evidence->fresh()->status);
        $this->assertSame(0, ReasonRejection::count());
    }
}
