<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // CI does not compile the assets, so @vite must not look for the manifest in tests.
        $this->withoutVite();
    }

    /**
     * Hash of every row in every table, to assert that a request left the database untouched.
     */
    protected function databaseSnapshot(): string
    {
        $data = [];
        foreach (DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name") as $table) {
            // The error recorder keeps a failed action on purpose, outside the transaction that rolled back
            if (in_array($table->name, ['error_reports', 'error_occurrences'], true)) {
                continue;
            }
            $rows = DB::table($table->name)->orderBy('rowid')->get();
            // ...and its autoincrement counters
            if ($table->name === 'sqlite_sequence') {
                $rows = $rows->reject(fn ($row): bool => in_array($row->name, ['error_reports', 'error_occurrences'], true))->values();
            }
            $data[$table->name] = $rows->all();
        }

        return hash('sha256', serialize($data));
    }
}
