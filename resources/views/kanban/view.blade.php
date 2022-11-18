@extends('layouts.app')

@section('title', 'Ver kanban: '.$kanban->title)

@section('title-icon', 'fab fa-battle-net')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{route('kanban.list',$instance)}}">Mis tableros</a></li>

    <li class="breadcrumb-item active">@yield('title')</li>
@endsection