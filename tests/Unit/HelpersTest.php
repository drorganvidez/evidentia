<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function test_random_identifiers_are_digits_of_the_given_length(): void
    {
        $identifiers = array_map(fn () => \Random::getRandomIdentifier(4), range(1, 200));

        foreach ($identifiers as $identifier) {
            $this->assertMatchesRegularExpression('/^\d{4}$/', $identifier);
        }
        $this->assertGreaterThan(150, count(array_unique($identifiers)));
        $this->assertMatchesRegularExpression('/^\d{16}$/', \Random::getRandomIdentifier());
    }

    public function test_clean_names_keep_only_uppercase_letters_and_digits(): void
    {
        $this->assertSame('PEREZLOPEZ', \StringUtilites::clean(' Pérez López '));
        $this->assertSame('NUNEZ', \StringUtilites::clean('Núñez'));
        $this->assertSame('OBRIEN2', \StringUtilites::clean("O'Brien 2"));
        $this->assertSame('SMITHCO', \StringUtilites::clean('Smith & Co'));
    }

    public function test_durations_split_into_hours_and_minutes(): void
    {
        $this->assertSame(1, \Time::complex_shape_hours(1.5));
        $this->assertEquals(30, \Time::complex_shape_minutes(1.5));
        $this->assertEquals(0, \Time::complex_shape_minutes(2));
        $this->assertNull(\Time::complex_shape_hours('dos'));
    }
}
