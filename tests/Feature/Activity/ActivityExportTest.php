<?php

namespace Tests\Feature\Activity;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\Activity\ActivityExport;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The CSV of the activity log: only for the management, readable by a spreadsheet and never a formula. */
class ActivityExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(E2eSeeder::class);
    }

    public function test_the_management_downloads_the_log_and_it_is_annotated(): void
    {
        $lecturer = User::where('username', 'profesor')->sole();
        Activity::record(ActivityAction::ConfigChanged, actor: $lecturer, actingRole: 'LECTURE', subjectTitle: '=HYPERLINK("http://x")');

        $response = $this->actingAs($lecturer)->get(route('activity.export', ['type' => 'group:management']));

        $response->assertOk();
        $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));
        $this->assertMatchesRegularExpression('/attachment; filename=registro-actividad-\d{8}-\d{4}\.csv/', $response->headers->get('Content-Disposition'));
        $csv = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF".implode(';', ActivityExport::HEADERS), $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString('127.0.0.1', $csv);
        $this->assertStringContainsString('committee.edited', $csv);
        $this->assertStringNotContainsString('evidence.rejected', $csv);

        $export = ActivityLog::where('action', 'export.activity')->sole();
        $this->assertSame('LECTURE', $export->acting_role);
        $this->assertSame(['type' => 'group:management'], $export->details['filters']);
        $this->assertSame(2, $export->details['rows']);
    }

    public function test_only_the_management_downloads_it(): void
    {
        foreach (['coordinador', 'secretario', 'estudiante'] as $who) {
            $this->actingAs(User::where('username', $who)->sole())->get(route('activity.export'))->assertNotFound();
        }
        $this->assertSame(0, ActivityLog::where('action', 'export.activity')->count());
    }

    public function test_a_cell_never_starts_a_formula(): void
    {
        foreach (['=1+1', '+1', '-1', '@SUM(A1)', "\tx", "\rx"] as $value) {
            $this->assertSame("'".$value, ActivityExport::cell($value));
        }
        $this->assertSame('Montaje', ActivityExport::cell('Montaje'));
        $this->assertSame('', ActivityExport::cell(null));
        $this->assertSame('7', ActivityExport::cell(7));
    }
}
