<div class="col-3">

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                    <a href="{{route('secretary.meeting.manage.request',\Instantiation::instance())}}" class="nav-link">

                        @if(Route::currentRouteName() == 'secretary.meeting.manage.request')
                            <b>
                        @endif

                         Crear convocatoria

                        @if(Route::currentRouteName() == 'secretary.meeting.manage.request')
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
                    <a href="#" class="nav-link">
                        Mis convocatorias
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
