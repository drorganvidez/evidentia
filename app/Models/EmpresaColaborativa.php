<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresaColaborativa extends Model
{
    protected $table = "empresaColaborativa";
    
    protected $fillable = ["id", "name", "telephone","email"];
    
}