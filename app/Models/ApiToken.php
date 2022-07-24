<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    use HasFactory;

    protected $table = "api_tokens";

    protected $fillable = ["user_id", "name", "token"];

    protected $hidden = [
        'token',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
