<div class="col-3">

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <ul class="nav nav-pills flex-column">

                <li class="nav-item">
                    <a href="{{route('secretary.meeting.manage.request.create',\Instantiation::instance())}}" class="nav-link">

                        @if(Route::currentRouteName() == 'secretary.meeting.manage.request.create')
                            <b>
                        @endif

                         Crear convocatoria

                        @if(Route::currentRouteName() == 'secretary.meeting.manage.request.create')
                            </b>
                        @endif

                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{route('secretary.meeting.manage.signaturesheet.create',\Instantiation::instance())}}" class="nav-link">

                        @if(Route::currentRouteName() == 'secretary.meeting.manage.signaturesheet.create')
                            <b>
                                @endif

                                Crear hoja de firmas

                                @if(Route::currentRouteName() == 'secretary.meeting.manage.signaturesheet.create')
                            </b>
                        @endif

                    </a>
                </li>


                <li class="nav-item">
                    <a href="#" class="nav-link">
                        Crear acta
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <ul class="nav nav-pills flex-column">

                <li class="nav-item">
                    <a href="{{route('secretary.meeting.manage.request.list',\Instantiation::instance())}}" class="nav-link">


                        @if(Route::currentRouteName() == 'secretary.meeting.manage.request.list')
                            <b>
                        @endif

                    Mis convocatorias

                        @if(Route::currentRouteName() == 'secretary.meeting.manage.request.list')
                            </b>
                        @endif

                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{route('secretary.meeting.manage.signaturesheet.list',\Instantiation::instance())}}" class="nav-link">


                        @if(Route::currentRouteName() == 'secretary.meeting.manage.signaturesheet.list')
                            <b>
                                @endif

                                Mis hojas de firmas

                                @if(Route::currentRouteName() == 'secretary.meeting.manage.signaturesheet.list')
                            </b>
                        @endif

                    </a>
                </li>



                <li class="nav-item">
                    <a href="#" class="nav-link">
                        Mis actas
                    </a>
                </li>
            </ul>
        </div>
    </div>

</div>
