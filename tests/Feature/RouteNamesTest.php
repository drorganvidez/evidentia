<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteNamesTest extends TestCase
{
    public function test_route_names_are_unique_so_routes_can_be_cached(): void
    {
        $names = collect(Route::getRoutes()->getRoutes())->map->getName()->filter();

        $this->assertSame([], $names->duplicates()->values()->all());
    }

    public function test_renamed_routes_keep_their_urls(): void
    {
        $this->assertSame(url('attendees'), route('registercoordinator.attendee.list'));
        $this->assertSame(url('password/update/abc'), route('password.custom_update', 'abc'));
    }
}
