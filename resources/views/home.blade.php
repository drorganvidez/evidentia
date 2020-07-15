@extends('layouts.app')

@section('title', 'Bienvenid@')
@section('title-icon', 'fas fa-home')

@section('content')


    <div class="row">

        <div class="col-lg-6 col-sm-12">

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Resumen de mis evidencias</h3>
                </div>

                <div class="card-body pb-0">

                    <div class="row">
                        <div class="col-lg-12 col-sm-12">
                            <x-infoevidencetotalcount :user="Auth::user()" />
                        </div>
                        <div class="col-lg-12 col-sm-12">
                            <x-infoevidencetotalhours :user="Auth::user()" />
                        </div>

                        <div class="col-lg-6 col-sm-12">
                            <x-infoevidencetotalcountdraft :user="Auth::user()" />
                        </div>
                        <div class="col-lg-6 col-sm-12">
                            <x-infoevidencetotalcountpending :user="Auth::user()" />
                        </div>
                        <div class="col-lg-6 col-sm-12">
                            <x-infoevidencetotalcountaccepted :user="Auth::user()" />
                        </div>
                        <div class="col-lg-6 col-sm-12">
                            <x-infoevidencetotalcountrejected :user="Auth::user()" />
                        </div>
                    </div>

                </div>

            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Reuniones y eventos</h3>
                </div>

                <div class="card-body pb-0">

                    <div class="row">

                        <div class="col-lg-6 col-sm-12">
                            <x-infomeetingcount :user="Auth::user()" />
                        </div>

                        <div class="col-lg-6 col-sm-12">
                            <x-infomeetinghours :user="Auth::user()" />
                        </div>

                        <div class="col-lg-12 col-sm-12">
                            <x-infobonushours :user="Auth::user()" />
                        </div>

                        <div class="col-lg-6 col-sm-12">
                            <x-infomeetinghours :user="Auth::user()" />
                        </div>

                        <div class="col-lg-12 col-sm-12">
                            <x-infomeetinghours :user="Auth::user()" />
                        </div>

                    </div>

                </div>
            </div>


        </div>

        <div class="col-lg-6 col-sm-12">

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Jornadas InnoSoft Days</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body p-0">
                    <ul class="users-list clearfix">
                        <li>
                            <img src="dist/img/user1-128x128.jpg" alt="User Image">
                            <a class="users-list-name" href="#">Alexander Pierce</a>
                            <span class="users-list-date">Today</span>
                        </li>
                        <li>
                            <img src="dist/img/user8-128x128.jpg" alt="User Image">
                            <a class="users-list-name" href="#">Norman</a>
                            <span class="users-list-date">Yesterday</span>
                        </li>
                        <li>
                            <img src="dist/img/user7-128x128.jpg" alt="User Image">
                            <a class="users-list-name" href="#">Jane</a>
                            <span class="users-list-date">12 Jan</span>
                        </li>
                        <li>
                            <img src="dist/img/user6-128x128.jpg" alt="User Image">
                            <a class="users-list-name" href="#">John</a>
                            <span class="users-list-date">12 Jan</span>
                        </li>
                        <li>
                            <img src="dist/img/user2-160x160.jpg" alt="User Image">
                            <a class="users-list-name" href="#">Alexander</a>
                            <span class="users-list-date">13 Jan</span>
                        </li>
                        <li>
                            <img src="dist/img/user5-128x128.jpg" alt="User Image">
                            <a class="users-list-name" href="#">Sarah</a>
                            <span class="users-list-date">14 Jan</span>
                        </li>
                        <li>
                            <img src="dist/img/user4-128x128.jpg" alt="User Image">
                            <a class="users-list-name" href="#">Nora</a>
                            <span class="users-list-date">15 Jan</span>
                        </li>
                        <li>
                            <img src="dist/img/user3-128x128.jpg" alt="User Image">
                            <a class="users-list-name" href="#">Nadia</a>
                            <span class="users-list-date">15 Jan</span>
                        </li>
                    </ul>
                    <!-- /.users-list -->
                </div>
                <!-- /.card-body -->
                <!-- /.card-footer -->
            </div>

        </div>

    </div>

@endsection
