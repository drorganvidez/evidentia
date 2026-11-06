<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
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
        'message_threads', 'messages', 'message_thread_reads', 'error_reports', 'error_occurrences',
    ];

    public function test_the_migrations_of_the_phase_roll_back_and_run_again(): void
    {
        $steps = count(File::glob(database_path('migrations/2026_11_06_*.php')));
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
        $this->assertEquals($expected, $this->schema());

        $this->assertSame(0, Artisan::call('migrate'));

        $this->assertEquals($before, $this->schema());
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
