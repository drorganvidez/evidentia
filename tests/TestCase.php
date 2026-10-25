<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    /**
     * Hash of every row in every table, to assert that a request left the database untouched.
     */
    protected function databaseSnapshot(): string
    {
        $data = [];
        foreach (DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name") as $table) {
            $data[$table->name] = DB::table($table->name)->orderBy('rowid')->get()->all();
        }

        return hash('sha256', serialize($data));
    }
}
