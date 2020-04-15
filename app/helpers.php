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
}

/*
 *  SELLO DE UN ARCHIVO
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

    public static function compute_evidence($evidence)
    {
        $salt =  \Config::secret();
        $evidence->stamp = hash('sha256',
                $evidence->name.
                    $evidence->description.
                    $evidence->hours.
                    $evidence->created_at.
                    $evidence->upload_at.
                    $salt);
        return $evidence;
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

    public static function lower_upload_evidences_timestamp()
    {
        return self::config_entity()->lower_upload_evidences_timestamp;
    }

    public static function upper_upload_evidences_timestamp()
    {
        return self::config_entity()->upper_upload_evidences_timestamp;
    }

    public static function lower_validate_evidences_timestamp()
    {
        return self::config_entity()->lower_validate_evidences_timestamp;
    }

    public static function upper_validate_evidences_timestamp()
    {
        return self::config_entity()->upper_validate_evidences_timestamp;
    }

    public static function lower_register_meeting_timestamp()
    {
        return self::config_entity()->lower_register_meeting_timestamp;
    }

    public static function upper_register_meeting_timestamp()
    {
        return self::config_entity()->upper_register_meeting_timestamp;
    }

    public static function lower_allegations_timestamp()
    {
        return self::config_entity()->lower_allegations_timestamp;
    }

    public static function upper_allegations_timestamp()
    {
        return self::config_entity()->upper_allegations_timestamp;
    }

    public static function lower_register_bonus_timestamp()
    {
        return self::config_entity()->lower_register_bonus_timestamp;
    }

    public static function upper_register_bonus_timestamp()
    {
        return self::config_entity()->upper_register_bonus_timestamp;
    }

    public static function secret()
    {
        return self::config_entity()->secret;
    }

}


?>
