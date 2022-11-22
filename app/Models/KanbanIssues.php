<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KanbanIssues extends Model
{

    protected $table="kanban_issues";

    protected $fillable = [
        'task', 'description', 'hours', 'user_id', 'comittee_id', 'type'
    ];


    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function comittee()
    {
        return $this->belongsTo('App\Models\Comittee');
    }


    public static function issues_to_do() {
        return KanbanIssues::where('type','=', 'TODO')->get();
    }

    public static function issues_in_progress() {
        return KanbanIssues::where('type','=', 'INPROGRESS')->get();
    }

    public static function issues_closed() {
        return KanbanIssues::where('type','=', 'CLOSED')->get();
    }

}
