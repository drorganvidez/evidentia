<?php

namespace App\Http\Livewire;

use App\Http\Services\InstanceService;
use Livewire\Component;

class InstanceConnection extends Component
{

    protected $listeners = [
        'testConnection' => 'testConnection'
    ];

    public function render()
    {
        return view('livewire.instance-connection');
    }

    public function testConnection($jsonString)
    {
        $info = json_decode($jsonString, true);
        $name = $info['name'];
        $route = $info['route'];
        $host = $info['host'];
        $port = $info['port'];
        $username = $info['username'];
        $password = $info['password'];

        $instance_service = new InstanceService();

        $successfully_connection = $instance_service->test_connection(
            $name,
            $route,
            $host,
            $port,
            $username,
            $password);

        $this->emit('testConnectionResult', $successfully_connection);
    }
}
