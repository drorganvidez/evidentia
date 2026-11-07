<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The migrations of the traceability phase go back and forth: rolling them back leaves the tables as they were
 * before them and running them again leaves them as they are now.
 */
class MigrationsTest extends TestCase
{
    use RefreshDatabase;

    private const NEW_TABLES = [
        'activity_log', 'activity_log_targets', 'notifications', 'notification_preferences', 'notification_reminders',
        'message_threads', 'messages', 'message_thread_reads', 'error_reports', 'error_occurrences', 'certificates',
        'evidence_coauthors',
    ];

    public function test_the_migrations_of_the_phase_roll_back_and_run_again(): void
    {
        $steps = count(File::glob(database_path('migrations/2026_11_0[67]_*.php')));
        $this->assertGreaterThanOrEqual(4, $steps);
        $before = $this->schema();

        $this->assertSame(0, Artisan::call('migrate:rollback', ['--step' => $steps]));

        foreach (self::NEW_TABLES as $table) {
            $this->assertFalse(Schema::hasTable($table), $table);
        }
        $this->assertFalse(Schema::hasIndex('evidences', 'evidences_review_queue_index'));
        $expected = array_diff_key($before, array_flip(self::NEW_TABLES));
        $expected['evidences']['indexes'] = array_values(array_filter(
            $expected['evidences']['indexes'],
            fn (array $index): bool => $index['name'] !== 'evidences_review_queue_index',
        ));
        $expected['committees']['columns'] = array_values(array_filter(
            $expected['committees']['columns'],
            fn (array $column): bool => $column['name'] !== 'presidency',
        ));
        $this->assertFalse(Schema::hasColumn('committees', 'presidency'));
        $expected['configuration']['columns'] = array_values(array_filter(
            $expected['configuration']['columns'],
            fn (array $column): bool => ! in_array($column['name'], ['max_evidences_hours', 'max_attendees_hours'], true),
        ));
        $expected['events']['columns'] = array_values(array_filter(
            $expected['events']['columns'],
            fn (array $column): bool => $column['name'] !== 'event_group',
        ));
        $expected['events']['indexes'] = array_values(array_filter(
            $expected['events']['indexes'],
            fn (array $index): bool => $index['name'] !== 'events_event_group_index',
        ));
        $this->assertEquals($expected, $this->schema());

        $this->assertSame(0, Artisan::call('migrate'));

        $this->assertEquals($before, $this->schema());
        // the committee of the presidency outlives the rollback and is marked again, never twice
        $this->assertSame(1, DB::table('committees')->where('name', 'Presidencia')->count());
        $this->assertSame(1, DB::table('committees')->where('presidency', true)->count());
    }

    public function test_a_fresh_course_gets_the_committee_of_the_presidency(): void
    {
        $presidency = DB::table('committees')->where('presidency', true)->sole();

        $this->assertSame('Presidencia', $presidency->name);
        $this->assertSame('crown', $presidency->icon);
    }

    public function test_the_committee_called_presidencia_of_a_course_is_marked_and_nothing_else_changes(): void
    {
        $migration = require database_path('migrations/2026_11_06_000007_add_presidency_to_committees_table.php');
        $migration->down();
        DB::table('committees')->delete();
        DB::table('committees')->insert([
            ['id' => 1, 'name' => 'Logística', 'icon' => 'warehouse', 'created_at' => '2026-01-01 10:00:00', 'updated_at' => '2026-01-01 10:00:00'],
            ['id' => 2, 'name' => 'Presidencia', 'icon' => '<i class="fas fa-user-tie"></i>', 'created_at' => '2026-01-01 10:00:00', 'updated_at' => '2026-01-01 10:00:00'],
        ]);
        $before = DB::table('committees')->orderBy('id')->get()->map(fn (object $row): array => (array) $row)->all();

        $migration->up();
        $migration->up();

        $after = DB::table('committees')->orderBy('id')->get()->map(fn (object $row): array => (array) $row)->all();
        $this->assertCount(2, $after);
        $this->assertSame([false, true], array_map(fn (array $row): bool => (bool) $row['presidency'], $after));
        $this->assertEquals($before, array_map(fn (array $row): array => array_diff_key($row, ['presidency' => true]), $after));
    }

    /**
     * Columns, indexes and foreign keys of every table of the app. The single column indexes that only back a foreign
     * key are left out: MySQL creates, drops and renames them on its own.
     */
    private function schema(): array
    {
        $schema = [];
        foreach (Schema::getTableListing(schemaQualified: false) as $table) {
            if ($table === 'migrations') {
                continue;
            }
            $foreign = Schema::getForeignKeys($table);
            $backing = array_map(fn (array $key): array => $key['columns'], $foreign);
            $indexes = array_values(array_filter(
                Schema::getIndexes($table),
                fn (array $index): bool => $index['primary'] || $index['unique'] || ! in_array($index['columns'], $backing, true),
            ));
            usort($indexes, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));
            $schema[$table] = [
                'columns' => Schema::getColumns($table),
                'indexes' => $indexes,
                'foreign' => $foreign,
            ];
        }
        ksort($schema);

        return $schema;
    }
}
