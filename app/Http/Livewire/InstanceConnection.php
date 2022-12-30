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
        /*
        // Procesa los datos enviados mediante la solicitud Ajax
        $name = $this->input('name');
        $host = $this->input('host');
        $database = $this->input('database');
        $port = $this->input('port');
        $username = $this->input('username');
        $password = $this->input('password');

        // Aquí puedes hacer lo que necesites con los datos procesados,
        // como almacenarlos en la base de datos o realizar una consulta a otro servicio

        // Actualiza la vista con los datos procesados
        $this->emit('updateView', $name, $host, $database, $port, $username, $password);
        */

        $this->emit('testConnectionResult', $successfully_connection);
    }
}
