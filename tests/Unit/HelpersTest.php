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

    public function test_field_ids_are_deterministic_and_safe_for_html(): void
    {
        $this->assertSame('field-users', ui_field_id('users[]'));
        $this->assertSame('field-points-json', ui_field_id('points_json'));
        $this->assertSame('field-a-b', ui_field_id('a[b]'));
        $this->assertSame('field-roles-0', ui_field_id('roles[0]'));
        $this->assertSame('field-title', ui_field_id('title'));
        $this->assertSame('field-correo-electronico', ui_field_id('Correo electrónico'));
        $this->assertSame(ui_field_id('users[]'), ui_field_id('users[]'));
    }

    public function test_error_keys_follow_the_dot_notation_of_the_validator(): void
    {
        $this->assertSame('users', ui_error_key('users[]'));
        $this->assertSame('a.b', ui_error_key('a[b]'));
        $this->assertSame('a.b', ui_error_key('a[b][]'));
        $this->assertSame('roles.0', ui_error_key('roles[0]'));
        $this->assertSame('title', ui_error_key('title'));
    }
}
