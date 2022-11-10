@extends('layouts.app')

@section('title', 'Ver tarea: '.$task->title)

@section('title-icon', 'fab fa-battle-net')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    
    <li class="breadcrumb-item"><a href="{{route('task.list',$instance)}}">Mis tareas</a></li>


    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-8">

            <div class="card shadow-lg">

                <div class="card-body">

                    <h5>
                        <x-taskcomittee :task="$task"/>
                        <span class="badge badge-secondary">
                            <i class="far fa-clock"></i> {{$task->hours}} horas
                        </span>
                    </h5>

                    <h4>{{$task->title}}</h4>

                    <div class="post text-justify">

                        {!! $task->description !!}

                        <br><br>

                        <!--
                        <a class="btn btn-info btn-sm"
                            href="{{route('evidence.edit',['instance' => $instance, 'id' => $task->id])}}">
                            <i class="fas fa-pencil-alt">
                            </i>
                        </a>


                        <x-buttonconfirm :id="$task->id" route="evidence.remove" title="¿Seguro?" description="Esto borrará la evidencia actual, las
                                        ediciones anteriores <b>y todos los archivos adjuntos.</b>" type="REMOVE"/>

-->
                        


                    </div>



                </div>

            </div>

        </div>

        <div class="col-lg-4">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h4>Estado</h4>
                    <!--
                    Añadir
                    -->
                </div>

            </div>

        </div>

    </div>


@endsection
