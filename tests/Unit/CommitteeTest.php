<?php

namespace Tests\Unit;

use App\Http\Services\CommitteeService;
use App\Http\Services\EvidenceService;
use App\Http\Services\Service;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CommitteeTest extends TestCase
{

    private Service $committeeService;

    public function __construct()
    {
        parent::__construct();
        $this->committeeService = new CommitteeService();
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

    public function testCreateCommitteeSuccess()
    {

        $committee_data = [
            'name' => 'Nuevo comité',
            'icon' => 'fa fas-envelope'];

        $committee = $this->committeeService->create($committee_data);

        $this->assertNotNull($committee->name);

        $committee->delete();

    }

    public function testCreateCommitteeFailNoName()
    {

        $committee_data = [
            'name' => '',
            'icon' => 'fa fas-envelope'];

        $committee = $this->committeeService->create($committee_data);

        $this->assertNull($committee);

    }

    public function testCreateCommitteeFailNoIcon()
    {

        $committee_data = [
            'name' => 'Nuevo titulo',
            'icon' => ''];

        $committee = $this->committeeService->create($committee_data);

        $this->assertNull($committee);

    }

}
