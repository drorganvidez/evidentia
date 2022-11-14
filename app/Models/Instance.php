<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instance extends Model
{
    protected $fillable = ["id", "name", "route", "host", "port", "username", "password", "database"];
}

