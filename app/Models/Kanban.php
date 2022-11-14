<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kanban extends Model
{
    protected $table="kanban";

    protected $fillable = [
        'id', 'title', 'user_id', 'comitte_id'
    ];

}

