<?php

use App\Models\Configuration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

/*
 *  SELLO DE UN ARCHIVO Y DE UNA EVIDENCIA
 */

class Stamp
{
    public static function compute_file($file)
    {
        $salt = Config::secret();
        $hash_file = hash_file('sha256', Storage::disk('local')->path($file->route));
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
        $salt = Config::secret();
        // el mismo disco en el que se calculó el sello; un archivo que falta no es íntegro
        $path = Storage::disk('local')->path($file->route);
        $hash_file = is_file($path) ? hash_file('sha256', $path) : '';

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
        $salt = Config::secret();
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
        $salt = Config::secret();

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

    public static function max_attendees_hours()
    {
        return self::config_entity()->max_attendees_hours;
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

class Time
{
    // extrae las horas (enteras) del campo hours
    public static function complex_shape_hours($hours)
    {
        if (is_numeric($hours)) {
            return intval($hours);
        }
    }

    // extrae los minutos del campo hours
    public static function complex_shape_minutes($hours)
    {
        if (is_numeric($hours)) {
            return round((($hours - intval($hours))) * 60);
        }
    }
}

/*
 *  HTML ENRIQUECIDO (SUMMERNOTE)
 */

class Html
{
    private static ?HTMLPurifier $purifier = null;

    // deja solo las etiquetas de formato del editor, sin scripts ni atributos de eventos
    public static function clean(?string $html): string
    {
        if (self::$purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('Cache.DefinitionImpl', null);
            $config->set('HTML.Allowed', 'p[style],br,b,strong,i,em,u,s,strike,sub,sup,span[style],ul,ol,li,'
                .'a[href|title|target],h1,h2,h3,h4,h5,h6,blockquote,pre,code,hr,'
                .'table,thead,tbody,tr,th[colspan|rowspan],td[colspan|rowspan],img[src|alt|width|height|style]');
            $config->set('CSS.AllowedProperties', 'color,background-color,text-align,font-weight,font-style,text-decoration,width,height');
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'data' => true]);
            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            self::$purifier = new HTMLPurifier($config);
        }

        return self::$purifier->purify($html ?? '');
    }
}

class StringUtilites
{
    public static function clean($string)
    {
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
        $tmp = '/tmp/'.$user->username.'/'.$token.'/';

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

if (! function_exists('evidentia_version')) {
    function evidentia_version(): ?string
    {
        $path = base_path('.version');

        return file_exists($path) ? trim(file_get_contents($path)) : null;
    }
}
