<?php

namespace Tests\Feature;

use App\Models\Evidence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The versions of an evidence form a chain through points_to: the first one is the root, the last one the head.
 */
class EvidenceChainTest extends TestCase
{
    use RefreshDatabase;

    private function chain(): array
    {
        $student = User::factory()->create();
        $first = Evidence::create(['title' => 'v1', 'status' => 'REJECTED', 'user_id' => $student->id, 'last' => false]);
        $second = Evidence::create(['title' => 'v2', 'status' => 'REJECTED', 'user_id' => $student->id, 'points_to' => $first->id, 'last' => false]);
        $third = Evidence::create(['title' => 'v3', 'status' => 'PENDING', 'user_id' => $student->id, 'points_to' => $second->id]);

        return [$first, $second, $third];
    }

    public function test_every_version_finds_the_root_and_the_head(): void
    {
        [$first, $second, $third] = $this->chain();

        foreach ([$first, $second, $third] as $version) {
            $this->assertTrue($version->rootEvidence()->is($first));
            $this->assertTrue($version->headEvidence()->is($third));
        }
    }

    public function test_the_versions_are_numbered_from_one(): void
    {
        [$first, $second, $third] = $this->chain();

        $this->assertSame(1, $first->versionNumber());
        $this->assertSame(2, $second->versionNumber());
        $this->assertSame(3, $third->versionNumber());
    }

    public function test_a_single_version_is_its_own_root_and_head(): void
    {
        $evidence = Evidence::create(['title' => 'Única', 'status' => 'DRAFT', 'user_id' => User::factory()->create()->id]);

        $this->assertTrue($evidence->rootEvidence()->is($evidence));
        $this->assertTrue($evidence->headEvidence()->is($evidence));
        $this->assertSame(1, $evidence->versionNumber());
    }

    public function test_a_broken_chain_gives_the_evidence_itself_as_head(): void
    {
        $evidence = Evidence::create(['title' => 'Sin cabeza', 'status' => 'REJECTED', 'user_id' => User::factory()->create()->id, 'last' => false]);

        $this->assertTrue($evidence->headEvidence()->is($evidence));
    }
}
