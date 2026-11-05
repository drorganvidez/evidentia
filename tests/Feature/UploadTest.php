<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_loading_an_uploaded_file_that_is_gone_is_not_found(): void
    {
        Storage::fake();

        $this->actingAs(User::factory()->create())
            ->get(route('upload.load', ['file_name' => 'missing.pdf']))
            ->assertNotFound();
    }
}
