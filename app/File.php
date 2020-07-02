<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{

    protected $table = "files";

    protected $fillable = [
        'name', 'type', 'route', 'size'
    ];

    /*public function proof()
    {
        return $this->hasOne('App\Proof');
    }*/

    public function sizeForHuman()
    {
        $conversion = $this->size;
        $GB = 1000000000;
        $MB = 1000000;
        $KB = 1000;

        if($conversion > $GB)
        {
            return round($conversion/$GB,2)." GB";
        }

        if($conversion > $MB)
        {
            return round($conversion/$MB,2)." MB";
        }

        if($conversion > $KB)
        {
            return round($conversion/$KB,2)." KB";
        }

        return $conversion." KB";

    }

}
