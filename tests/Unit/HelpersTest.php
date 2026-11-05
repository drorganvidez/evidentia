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
}
