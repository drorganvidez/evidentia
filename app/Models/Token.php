<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $table = "tokens";

    protected $fillable = ["token", "used", "valid_until_timestamp", "user_id"];

    public function is_valid()
    {
        /**
         *  Un token es válido si:
         *      1. No ha sido usado
         *      2. Aún no ha expirado
         */


        // 1. No ha sido usado
        if(!$this->used){

            // 2. Aún no ha expirado
            $now = Carbon::now();
            $datetime = $this->valid_until_timestamp;

            if($now->lt($datetime)){

                return true;

            }

        }


        return false;
    }

}
