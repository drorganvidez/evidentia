<?php

use Illuminate\Support\Facades\Artisan;use Illuminate\Support\Str;

/*
 *  CONEXIONES CON LAS DISTINTAS BASES
 *  Se ofrece una forma fácil y segura de alternar entre las distintas
 *  bases de Evidentia.
 */

function default_connection()
{
    Artisan::call('config:clear');
    config(['database.default' => 'mysql']);
}

function default_instance()
{
    Artisan::call('config:clear');
    config(['database.default' => 'instance']);
}

function set($instance)
{
    config(['database.connections.instance' => [
        'driver' => 'mysql',
        'host' => $instance->host,
        'database' => $instance->database,
        'port' => $instance->port,
        'username' => $instance->username,
        'password' => $instance->password
    ]]);
    config(['database.default' => 'instance']);
}

/*
 *  OBTENER INFORMACIÓN DE LA INSTANCIA SEGÚN LA URL ACTUAL
 */

function instance()
{
    $url_current = url()->current();
    $collection = Str::of($url_current)->explode('/');
    $instance = $collection->all()[3];
    return $instance;
}

function instance_entity()
{
    $id = instance();
    default_connection();
    $entity = \App\Instance::where('id', $id)->first();
    default_instance();
    return $entity;
}

?>
