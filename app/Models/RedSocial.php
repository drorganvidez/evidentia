<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedSocial extends Model
{
    protected $table = "redSocial";
    
    protected $fillable = ["id", "name", "password"];
    
}