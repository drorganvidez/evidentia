@extends('layouts.app')

@section('title', 'Ver kanban: '.$kanban->title)

@section('title-icon', 'fab fa-battle-net')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{route('kanban.list',$instance)}}">Mis tableros</a></li>

    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <style>
        .kanban-centered {
            position: relative;
            margin-bottom: 30px;
        }
        .kanban-centered:before, .kanban-centered:after {
            content: " ";
            display: table;
        }

        .kanban-centered:after {
            clear: both;
        }

        .kanban-centered:before, .kanban-centered:after {
            content: " ";
            display: table;
        }

        .kanban-centered:after {
            clear: both;
        }

        .kanban-centered:before {
            content: '';
            position: absolute;
            display: block;
            width: 2px;
            top: 20px;
            bottom: 20px;
        }

        .kanban-centered .kanban-entry {
            position: relative;
            margin: 10px 8px;
            clear: both;
            border-radius: 4px;
            -webkit-box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.5);
            -moz-box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.5);
            box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.5);
        }

        .kanban-centered .kanban-entry:before, .kanban-centered .kanban-entry:after {
            content: " ";
            display: table;
        }

        .kanban-centered .kanban-entry:after {
            clear: both;
        }

        .kanban-centered .kanban-entry:before, .kanban-centered .kanban-entry:after {
            content: " ";
            display: table;
        }

        .kanban-centered .kanban-entry:after {
            clear: both;
        }

        .kanban-centered .kanban-entry.begin {
            margin-bottom: 0;
        }

        .kanban-centered .kanban-entry.left-aligned {
            float: left;
        }

        .kanban-centered .kanban-entry.left-aligned .kanban-entry-inner {
            margin-left: 0;
            margin-right: -18px;
        }

        .kanban-centered .kanban-entry.left-aligned .kanban-entry-inner .kanban-time {
            left: auto;
            right: -100px;
            text-align: left;
        }

        .kanban-centered .kanban-entry.left-aligned .kanban-entry-inner .kanban-icon {
            float: right;
        }

        .kanban-centered .kanban-entry.left-aligned .kanban-entry-inner .kanban-label {
            margin-left: 0;
            margin-right: 70px;
        }

        .kanban-centered .kanban-entry.left-aligned .kanban-entry-inner .kanban-label:after {
            left: auto;
            right: 0;
            margin-left: 0;
            margin-right: -9px;
            -moz-transform: rotate(180deg);
            -o-transform: rotate(180deg);
            -webkit-transform: rotate(180deg);
            -ms-transform: rotate(180deg);
            transform: rotate(180deg);
        }

        .kanban-centered .kanban-entry .kanban-entry-inner {
            position: relative;
            /*margin-left: -24px;*/
        }

        .kanban-centered .kanban-entry .kanban-entry-inner:before, .kanban-centered .kanban-entry .kanban-entry-inner:after {
            content: " ";
            display: table;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner:after {
            clear: both;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner:before, .kanban-centered .kanban-entry .kanban-entry-inner:after {
            content: " ";
            display: table;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner:after {
            clear: both;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner .kanban-time {
            position: absolute;
            left: -100px;
            text-align: right;
            padding: 10px;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner .kanban-time > span {
            display: block;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner .kanban-time > span:first-child {
            font-size: 15px;
            font-weight: bold;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner .kanban-time > span:last-child {
            font-size: 12px;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner .kanban-icon {
            background: #fff;
            color: #737881;
            display: block;
            width: 25px;
            height: 25px;
            -webkit-background-clip: padding-box;
            -moz-background-clip: padding;
            background-clip: padding-box;
            -webkit-border-radius: 20px;
            -moz-border-radius: 20px;
            border-radius: 20px;
            text-align: center;
            -moz-box-shadow: 0 0 0 4px #f5f5f6;
            -webkit-box-shadow: 0 0 0 4px #f5f5f6;
            box-shadow: 0 0 0 4px #f5f5f6;
            float: left;
            margin-top: 6px;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner .kanban-icon.bg-primary {
            background-color: #303641;
            color: #fff;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner .kanban-icon.bg-secondary {
            background-color: #ee4749;
            color: #fff;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner .kanban-icon.bg-success {
            background-color: #00a651;
            color: #fff;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner .kanban-icon.bg-info {
            background-color: #21a9e1;
            color: #fff;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner .kanban-icon.bg-warning {
            background-color: #fad839;
            color: #fff;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner .kanban-icon.bg-danger {
            background-color: #cc2424;
            color: #fff;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner .kanban-label {
            position: relative;
            background: #f5f5f6;
            padding: 0.75em;
            -webkit-background-clip: padding-box;
            -moz-background-clip: padding;
            background-clip: padding-box;
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            border-radius: 3px;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner .kanban-label h2, .kanban-centered .kanban-entry .kanban-entry-inner .kanban-label p {
            color: #737881;
            font-family: "Noto Sans",sans-serif;
            font-size: 12px;
            margin: 0;
            line-height: 1.428571429;
        }

            .kanban-centered .kanban-entry .kanban-entry-inner .kanban-label p + p {
                margin-top: 15px;
            }

        .kanban-centered .kanban-entry .kanban-entry-inner .kanban-label h2 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner .kanban-label h2 a {
            color: #303641;
        }

        .kanban-centered .kanban-entry .kanban-entry-inner .kanban-label h2 span {
            -webkit-opacity: .6;
            -moz-opacity: .6;
            opacity: .6;
            -ms-filter: alpha(opacity=60);
            filter: alpha(opacity=60);
        }

        .modal-static {
            position: fixed;
            top: 50% !important;
            left: 50% !important;
            margin-top: -100px;
            margin-left: -100px;
            overflow: visible !important;
        }

        .modal-static,
        .modal-static .modal-dialog,
        .modal-static .modal-content {
            width: 200px;
            height: 150px;
        }

        .modal-static .modal-dialog,
        .modal-static .modal-content {
            padding: 0 !important;
            margin: 0 !important;
        }

        .kanban-col {
            width: 300px;
            margin-right: 20px;
            float: left;
        }

        .panel-body {
            padding: 15px 0 0 0;
            overflow-y: auto;
        }

        .grab {
            cursor: -moz-grab;
            cursor: -webkit-grab;
        }

        .grabbing {
            cursor: -moz-grabbing;
            cursor: -webkit-grabbing;
        }

        .panel-heading {
            cursor: context-menu;
        }

        .panel-heading i {
            cursor: pointer;
        }
    </style>

    <div class="row">

    <div class="col-lg-12">
        <div class="row mb-3">
            <div class="col-lg-3 mt-1">
                <a href="{{route('kanban.create_issue',['instance'=>$instance, 'id' => $kanban->id])}}" class="btn btn-primary btn-block"><i class="fas fa-plus"></i> &nbsp;Crear nueva tarea</a>
            </div>
        </div>
    </div>

</div>
    <div class="container-fluid">
        <div id="sortableKanbanBoards" class="row">
            <div class="panel panel-primary kanban-col">

                <div class="panel-heading">
                    TODO
                </div>
                <div class="panel-body">
                    <div id="TODO" class="kanban-centered">

                        <article class="kanban-entry grab" id="item1" draggable="true">
                            <div class="kanban-entry-inner">
                                <div class="kanban-label">
                                    @foreach($issues as $issuecard)
                                        @if ($issuecard->status=="TO DO")
                                            <h2><a href="#">{{$issuecard->title}}</a></h2>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </article>

                    </div>
                </div>
            </div>

            <div class="panel panel-primary kanban-col">
                
                <div class="panel-heading">
                    IN PROGRESS
                </div>
                <div class="panel-body">
                    <div id="TODO" class="kanban-centered">

                        <article class="kanban-entry grab" id="item1" draggable="true">
                            <div class="kanban-entry-inner">
                                
                                <div class="kanban-label">
                                    @foreach($issues as $issuecard)
                                        @if ($issuecard->status=="IN PROGRESS")
                                            <h2><a href="#">{{$issuecard->title}}</a></h2>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>


            <div class="panel panel-primary kanban-col">
                
                <div class="panel-heading">
                    COMPLETED
                </div>
                <div class="panel-body">
                    <div id="TODO" class="kanban-centered">

                        <article class="kanban-entry grab" id="item1" draggable="true">
                            <div class="kanban-entry-inner">
                                <div class="kanban-label">
                                    @foreach($issues as $issuecard)
                                        @if ($issuecard->status=="COMPLETED")
                                            <h2><a href="#">{{$issuecard->title}}</a></h2>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection   