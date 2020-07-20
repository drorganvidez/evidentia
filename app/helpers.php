<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use App\Configuration;

/*
 *  CONEXIONES CON LAS DISTINTAS BASES
 *  Se ofrece una forma fácil y segura de alternar entre las distintas
 *  bases de Evidentia.
 */
class Instantiation
{

    public static function set_default_connection()
    {
        Artisan::call('config:clear');
        config(['database.default' => 'mysql']);
    }

    public static function set_default_instance()
    {
        Artisan::call('config:clear');
        config(['database.default' => 'instance']);
    }

    public static function set($instance)
    {
        config(['database.connections.instance' => [
            'driver' => 'mysql',
            'host' => $instance->host,
            'database' => $instance->database,
            'port' => $instance->port,
            'username' => $instance->username,
            'password' => $instance->password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'engine' => 'InnoDB',
        ]]);
        config(['database.default' => 'instance']);
    }

    public static function instance()
    {
        $instance = "";
        try
        {
            $url_current = url()->current();
            $collection = Str::of($url_current)->explode('/');
            $instance = $collection->all()[3];

        }catch (\Exception $e)
        {

        }

        return $instance;
    }

    public static function instance_entity()
    {
        $route = self::instance();
        self::set_default_connection();
        $entity = \App\Instance::where('route',$route)->first();
        self::set_default_instance();
        return $entity;
    }

    public static function migrate()
    {
        Artisan::call('migrate',
            [
                '--path' => 'database/migrations/instances',
                '--database' => 'instance'
            ]);
    }
}

/*
 *  SELLO DE UN ARCHIVO Y DE UNA EVIDENCIA
 */

class Stamp
{
    public static function compute_file($file)
    {
        $salt =  \Config::secret();
        $hash_file = hash_file('sha256', storage_path('/app/'.$file->route));
        $file->stamp = hash('sha256',
                $file->name.
                    $file->size.
                    $file->type.
                    $file->route.
                    $file->created_at.
                    $file->upload_at.
                    $hash_file.
                    $salt);
        return $file;
    }

    public static function get_stamp_file($file)
    {
        $salt =  \Config::secret();
        $hash_file = hash_file('sha256', storage_path('/app/'.$file->route));
        return hash('sha256',
            $file->name.
            $file->size.
            $file->type.
            $file->route.
            $file->created_at.
            $file->upload_at.
            $hash_file.
            $salt);
    }

    public static function compute_evidence($evidence)
    {
        $salt =  \Config::secret();
        $evidence->stamp = hash('sha256',
                $evidence->title.
                    $evidence->description.
                    $evidence->hours.
                    $evidence->created_at.
                    $evidence->upload_at.
                    $salt);
        return $evidence;
    }

    public static function get_stamp_evidence($evidence)
    {
        $salt =  \Config::secret();
        return hash('sha256',
            $evidence->title.
            $evidence->description.
            $evidence->hours.
            $evidence->created_at.
            $evidence->upload_at.
            $salt);
    }
}

class Config
{

    private static function config_entity()
    {
        return Configuration::all()->find(1);
    }

    public static function max_attachment_number()
    {
        return self::config_entity()->max_attachment_number;
    }

    public static function max_attachment_size()
    {
        return self::config_entity()->max_attachment_size;
    }

    public static function max_proof_number()
    {
        return self::config_entity()->max_proof_number;
    }

    public static function max_proof_size()
    {
        return self::config_entity()->max_proof_size;
    }

    public static function max_evidence_number()
    {
        return self::config_entity()->max_evidence_number;
    }

    public static function max_assist_number()
    {
        return self::config_entity()->max_assist_number;
    }

    // configuración de fechas límite

    public static function upload_evidences_timestamp()
    {
        return self::config_entity()->upload_evidences_timestamp;
    }

    public static function validate_evidences_timestamp()
    {
        return self::config_entity()->validate_evidences_timestamp;
    }

    public static function meetings_timestamp()
    {
        return self::config_entity()->meetings_timestamp;
    }

    public static function bonus_timestamp()
    {
        return self::config_entity()->bonus_timestamp;
    }

    public static function attendee_timestamp()
    {
        return self::config_entity()->attendee_timestamp;
    }

    public static function events_uploaded_timestamp()
    {
        return self::config_entity()->events_uploaded_timestamp;
    }

    public static function attendees_uploaded_timestamp()
    {
        return self::config_entity()->attendees_uploaded_timestamp;
    }

    public static function secret()
    {
        return self::config_entity()->secret;
    }

    public static function eventbrite_token()
    {
        return self::config_entity()->eventbrite_token;
    }

}


?>
