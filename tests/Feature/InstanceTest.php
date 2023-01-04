<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\InstancesController;
use App\Http\Services\InstanceService;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\Request;
use Tests\TestCase;

class InstanceTest extends TestCase
{
    use WithoutMiddleware;

    public function setUp() : void
    {
        // write code that executes before starting the tests
        parent::setUp();
        //$this->setAppToZero();
        $this->setDefaultInstanceConnection();
    }

    public function tearDown() : void
    {
        // write code that runs at the end of each test
        parent::tearDown();
    }

    public function test_admin_login_true()
    {
        // Arrange
        $request = new Request([
            'email' => 'admin@admin.com',
            'password' => 'admin'
        ]);
        $adminController = new AdminController();

        // Act
        $response = $adminController->login_p($request);

        // Assert
        $this->assertTrue($response->isRedirection());
        $this->assertEquals(route('admin.dashboard'), $response->headers->get('Location'));

    }

    public function test_admin_login_false()
    {
        // Arrange
        $request = new Request([
            'email' => 'admin@admin.com',
            'password' => 'invalid'
        ]);
        $adminController = new AdminController();

        // Act
        $response = $adminController->login_p($request);

        // Assert
        $this->assertTrue($response->isRedirection());
        $this->assertEquals(url()->previous(), $response->headers->get('Location'));
        $this->assertEquals('Las credenciales no son válidas.', session('error'));
    }

    public function test_list_instances()
    {
        // Arrange
        $instancesController = new InstancesController();

        // Act
        $response = $instancesController->list();

        // Assert
        $this->assertEquals('instances.list', $response->getName());
        $this->assertCount(1, $response->getData()['instances']);
    }

    public function test_create_view()
    {
        $instancesController = new InstancesController();
        $view = $instancesController->create();

        $this->assertEquals('instances.createandedit', $view->getName());
        $this->assertEquals(['action' => 'admin.instances.create_p'], $view->getData());
    }


}
