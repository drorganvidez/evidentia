<?php

namespace Tests\Unit;

use App\Support\Changes;
use PHPUnit\Framework\TestCase;

class ChangesTest extends TestCase
{
    public function test_only_the_changed_fields_are_kept_on_both_sides(): void
    {
        $before = ['title' => 'Montaje', 'hours' => 1.5, 'committee_id' => 2, 'committee_name' => 'Programa'];
        $after = ['title' => 'Montaje', 'hours' => 2.0, 'committee_id' => 3, 'committee_name' => 'Logística'];

        $this->assertSame([
            'before' => ['hours' => 1.5, 'committee_id' => 2, 'committee_name' => 'Programa'],
            'after' => ['hours' => 2.0, 'committee_id' => 3, 'committee_name' => 'Logística'],
        ], Changes::between($before, $after));
        $this->assertTrue(Changes::any($before, $after));
    }

    public function test_nothing_changed_gives_nothing(): void
    {
        $same = ['title' => 'Montaje', 'users' => [1, 2]];

        $this->assertSame([], Changes::between($same, $same));
        $this->assertFalse(Changes::any($same, $same));
    }

    public function test_a_field_that_appears_counts_as_a_change_from_null(): void
    {
        $this->assertSame(['before' => ['icon' => null], 'after' => ['icon' => 'users']], Changes::between([], ['icon' => 'users']));
    }
}
