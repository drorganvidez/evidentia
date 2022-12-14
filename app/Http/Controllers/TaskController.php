<?php

namespace App\Http\Controllers;

use App\Exports\MyTasksExport;
use App\Models\Comittee;
use App\Models\Task;
use App\Models\File;
use App\Models\Proof;
use App\Rules\CheckStartDate;
use App\Rules\CheckEndDate;
use App\Rules\CheckHoursAndMinutes;
use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class TaskController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:PRESIDENT|COORDINATOR|REGISTER_COORDINATOR|SECRETARY|STUDENT');
    }

    public function view($instance,$id)
    {
        $instance = \Instantiation::instance();
        $task = Task::find($id);

        return view('task.view',
            ['instance' => $instance, 'task' => $task]);
    }

    public function list()
    {

        $comittees = Comittee::all();
        $tasks = Task::where(['user_id' => Auth::id()])->get();
        $instance = \Instantiation::instance();

        return view('task.list',[
            'comittees' => $comittees,
            'tasks' => $tasks,
            'instance' => $instance,
            'route_new' => route('task.create',$instance)
        ]);
    }

    public function create(Request $request)
    {
        $instance = \Instantiation::instance();
        $task_new = $this->new_task($request);
        $task_new->save();
        return redirect()->route('task.list', $instance)->with('success', 'Tarea guardada con éxito.');

    }

    private function new_task($request)
    {

        // datos necesarios para crear tareas
        $user = Auth::user();
        $start_date = date_create($request->input('start_date'));
        $end_date = date_create($request->input('end_date'));

        $request->validate([
            'title' => 'required',
            'description' => ['required',new MaxCharacters(2000)],
            'start_date' => ['required', new CheckStartDate($end_date,$start_date)],
            'end_date' => ['required', new CheckEndDate($end_date,$start_date)]
        ]);

        // Calculates the difference between DateTime objects
        $interval = date_diff($start_date, $end_date);
    
        $days = intval($interval->format('%d'));
        $hours = floatval($interval->format('%H'));
        $minutes = intval($interval->format('%i'));

        $hours = $days*24 + $hours + floor(($minutes*100)/60)/100;
            
        // creación de una nueva tarea
        $task = Task::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'hours' => $hours,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'user_id' => $user->id,
            'comittee_id' => $request->input('comittee')
        ]);
        
        $task->save();

        return $task;
    }

    /****************************************************************************
     * EDIT A TASK
     ****************************************************************************/

    public function edit($instance,$id)
    {
        $user = Auth::user();
        $instance = \Instantiation::instance();
        $token = session()->token();

        $task = Task::find($id);
        $comittees = Comittee::all();

        // generamos un nuevo token
        session()->regenerate();

        return view('task.edit', ['task' => $task, 'instance' => $instance,
            'comittees' => $comittees,
            'route_save' => route('task.save',$instance)]);
    }


    public function save(Request $request)
    {

        $instance = \Instantiation::instance();
        $task = Task::find($request->input('id'));

        // datos necesarios para crear tareas
        $user = Auth::user();
      
        $start_date = date_create($request->input('start_date'));
        $end_date = date_create($request->input('end_date'));

        // Calculates the difference between DateTime objects
        $interval = date_diff($start_date, $end_date);

        $days = intval($interval->format('%d'));
        $hours = floatval($interval->format('%H'));
        $minutes = intval($interval->format('%i'));

        $hours = $days*24 + $hours + floor(($minutes*100)/60)/100;
        
        $request->validate([
            'title' => 'required',
            'description' => ['required',new MaxCharacters(2000)],
            'start_date' => ['required', new CheckStartDate($end_date,$start_date)],
            'end_date' => ['required', new CheckEndDate($end_date,$start_date)]
        ]);

        // modificación de los datos
        $task->title = $request->input('title');
        $task->description = $request->input('description');
        $task->hours = $hours;
        $task->start_date = $start_date;
        $task->end_date = $end_date;
        $task->user_id = $user->id;
        $task->comittee_id = $request->input('comittee');
        
        $task->save();

        return redirect()->route('task.list', $instance)->with('success', 'Tarea guardada con éxito.');
    }

    /****************************************************************************
     * REMOVE A TASK
     ****************************************************************************/

    public function remove(Request $request)
    {
        $id = $request->_id;
        $task = Task::find($id);
        $instance = \Instantiation::instance();

        $task->delete();

        return redirect()->route('task.list',$instance)->with('success', 'Tarea borrada con éxito.');
    }
    /****************************************************************************
     * EXPORT TASKS
     ****************************************************************************/

    public function export($instance, $ext)
    {
        try {
            ob_end_clean();
            if(!in_array($ext, ['csv', 'pdf', 'xlsx'])){
                return back()->with('error', 'Solo se permite exportar los siguientes formatos: csv, pdf y xlsx');
            }
            return Excel::download(new MyTasksExport(), 'mistareas-' . \Illuminate\Support\Carbon::now() . '.' . $ext);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }
}
