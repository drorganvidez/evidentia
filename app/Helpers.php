<?php

use App\Models\Instance;
use Dotenv\Exception\InvalidPathException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;use Illuminate\Support\Str;
use App\Models\Configuration;

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
        $entity = Instance::where('route',$route)->first();
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

    public static function compute_incidence($incidence)
    {
        $salt =  \Config::secret();
        $incidence->stamp = hash('sha256',
            $incidence->title.
            $incidence->description.
            $incidence->hours.
            $incidence->created_at.
            $incidence->upload_at.
            $salt);
        return $incidence;
    }
    public static function get_stamp_incidence($incidence)
    {
        $salt =  \Config::secret();
        return hash('sha256',
            $incidence->title.
            $incidence->description.
            $incidence->hours.
            $incidence->created_at.
            $incidence->upload_at.
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

    public static function max_attendees_hours()
    {
        return self::config_entity()->max_attendees_hours;
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

    public static function upload_incidences_timestamp()
    {
        return self::config_entity()->upload_incidences_timestamp;
    }

    public static function validate_incidences_timestamp()
    {
        return self::config_entity()->validate_incidences_timestamp;
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

class Time{

    // extrae las horas (enteras) del campo hours
    public static function complex_shape_hours($hours){
        if(is_numeric($hours)) return intval($hours);
    }

    // extrae los minutos del campo hours
    public static function complex_shape_minutes($hours){
        if(is_numeric($hours)) return round((($hours-intval($hours)))*60);
    }

}

class StringUtilites{

    public static function clean($string){
        return strtoupper(trim(preg_replace('~[^0-9a-z]+~i', '', preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'))), ' '));
    }
}

class Filepond
{

    public function getServerIdFromPath($path)
    {
        return Crypt::encryptString($path);
    }

    public function getPathFromServerId($serverId)
    {

        return Crypt::decryptString($serverId);
    }

    public static function getFilesFromTemporaryFolder()
    {
        $user = Auth::user();
        $token = session()->token();
        $instance = \Instantiation::instance();
        $tmp = $instance.'/tmp/'.$user->username.'/'.$token.'/';

        $collection = collect();

        foreach (Storage::files($tmp) as $filename) {

            $file_name = pathinfo($filename, PATHINFO_BASENAME);
            $collection->push($file_name);

        }

        return $collection;

    }
}

class Random
{
    public static function getRandomIdentifier($length = 16)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}


?>
