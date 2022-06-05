<?php

namespace Tests\Unit;

use App\Http\Services\EvidenceService;
use App\Http\Services\Service;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class EvidenceTest extends TestCase
{

    private Service $evidenceService;

    public function __construct()
    {
        parent::__construct();
        $this->evidenceService = new EvidenceService();
    }

    public function setUp() : void
    {
        parent::setUp();
        $this->init();
    }

    public function tearDown() : void
    {
        // write code that runs at the end of each test
    }

    public function testCreateEvidenceSuccess()
    {

        $evidence_data = [
            'title' => 'Evidence Test',
            'description' => 'This is an evidence test',
            'hours' => 3.5,
            'user_id' => 1,
            'comittee_id' => 2,
            'points_to' => 3,
            'status' => 1,
            'stamp' => '423f234g5345h465g74j6467j',
            'rand' => 0];

        $evidence = $this->evidenceService->create($evidence_data);

        $this->assertNotNull($evidence->title);

        $evidence->delete();

    }

    public function testCreateEvidenceFail()
    {

        $evidence_data = [
            'title' => '55',
            'description' => 'This is an evidence test',
            'hours' => 3.5,
            'user_id' => 1,
            'comittee_id' => 2,
            'points_to' => 3,
            'status' => 1,
            'stamp' => '423f234g5345h465g74j6467j',
            'rand' => 0];

        $evidence = $this->evidenceService->create($evidence_data);
        $this->assertNull($evidence);

    }

    public function testUpdateEvicenceSuccess()
    {

        $evidence_data = [
            'title' => 'Evidence Test',
            'description' => 'This is an evidence test',
            'hours' => 3.5,
            'user_id' => 1,
            'comittee_id' => 2,
            'points_to' => 3,
            'status' => 1,
            'stamp' => '423f234g5345h465g74j6467j',
            'rand' => 0];

        $evidence = $this->evidenceService->create($evidence_data);

        $new_data = [
          'title' => 'Nuevo titulo',
          'hours' => 10
        ];

        $updated_evidence = $this->evidenceService->update($evidence->id, $new_data);

        $this->assertEquals("Nuevo titulo", $updated_evidence->title);

    }

    public function testUpdateEvicenceFail()
    {

        $evidence_data = [
            'title' => 'Evidence Test',
            'description' => 'This is an evidence test',
            'hours' => 3.5,
            'user_id' => 1,
            'comittee_id' => 2,
            'points_to' => 3,
            'status' => 1,
            'stamp' => '423f234g5345h465g74j6467j',
            'rand' => 0];

        $evidence = $this->evidenceService->create($evidence_data);

        $new_data = [
            'title' => 'N',
            'hours' => 10
        ];

        $updated_evidence = $this->evidenceService->update($evidence->id, $new_data);

        $this->assertNull($updated_evidence);

    }

}
