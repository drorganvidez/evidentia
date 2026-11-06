<?php

namespace Tests\Feature\Activity;

use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

/**
 * Nothing in the app edits or deletes an annotation through the model; only the purge writes again, with the
 * query builder.
 */
class ActivityLogInsertOnlyTest extends TestCase
{
    use RefreshDatabase;

    private function annotation(): ActivityLog
    {
        return ActivityLog::create(['created_at' => now(), 'action' => 'evidence.created', 'subject_title' => 'Montaje']);
    }

    public function test_an_annotation_cannot_be_updated(): void
    {
        $log = $this->annotation();

        $this->expectException(LogicException::class);
        $log->update(['subject_title' => 'Otro título']);
    }

    public function test_an_annotation_cannot_be_deleted(): void
    {
        $log = $this->annotation();

        try {
            $log->delete();
            $this->fail('An annotation was deleted through the model.');
        } catch (LogicException) {
            $this->assertDatabaseHas('activity_log', ['id' => $log->id, 'subject_title' => 'Montaje']);
        }
    }

    public function test_the_purge_deletes_through_the_query_builder(): void
    {
        $log = $this->annotation();

        DB::table('activity_log')->where('id', $log->id)->delete();

        $this->assertDatabaseMissing('activity_log', ['id' => $log->id]);
    }

    public function test_the_casts_read_back_what_was_written(): void
    {
        $log = ActivityLog::create([
            'created_at' => now(), 'action' => 'evidence.rejected', 'details' => ['reason' => 'Falta el certificado.'],
            'flags' => ['self_review'], 'flagged' => true,
        ])->fresh();

        $this->assertSame(['reason' => 'Falta el certificado.'], $log->details);
        $this->assertSame(['self_review'], $log->flags);
        $this->assertTrue($log->flagged);
        $this->assertTrue($log->created_at->isToday());
    }
}
