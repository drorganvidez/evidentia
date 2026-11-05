<?php

namespace Tests\Unit;

use App\Rules\CheckHoursAndMinutes;
use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;
use PHPUnit\Framework\TestCase;

class RulesTest extends TestCase
{
    public function test_text_length_counts_visible_characters(): void
    {
        // 10 visible characters, two of them accented, wrapped in editor markup
        $text = '<p>Organicé ó</p>';

        $this->assertTrue((new MinCharacters(10))->passes('description', $text));
        $this->assertFalse((new MinCharacters(11))->passes('description', $text));
        $this->assertTrue((new MaxCharacters(10))->passes('description', $text));
        $this->assertFalse((new MaxCharacters(9))->passes('description', $text));
    }

    public function test_html_entities_count_as_one_character(): void
    {
        $this->assertTrue((new MaxCharacters(3))->passes('description', '<p>a&nbsp;b</p>'));
        $this->assertFalse((new MinCharacters(10))->passes('description', '<p>&nbsp;&nbsp;&nbsp;</p>'));
    }

    public function test_a_duration_needs_hours_or_minutes(): void
    {
        $this->assertFalse((new CheckHoursAndMinutes(0))->passes('hours', 0));
        $this->assertFalse((new CheckHoursAndMinutes(null))->passes('hours', null));
        $this->assertTrue((new CheckHoursAndMinutes(30))->passes('hours', 0));
        $this->assertTrue((new CheckHoursAndMinutes(0))->passes('hours', 2));
    }
}
