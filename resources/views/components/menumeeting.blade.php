<div class="col-lg-3">

    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title">Crear</h3>
        </div>
        <div class="card-body p-0">
            <ul class="nav nav-pills flex-column">

                <li class="nav-item">
                    <a href="{{route('secretary.meeting.manage.request.create',\Instantiation::instance())}}" class="nav-link">

                        @if(Route::currentRouteName() == 'secretary.meeting.manage.request.create')
                            <b>
                        @endif

                                <i class="fas fa-child"></i>&nbsp;&nbsp;Crear convocatoria

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

                                <i class="fas fa-signature"></i>&nbsp;&nbsp;Crear hoja de firmas

                                @if(Route::currentRouteName() == 'secretary.meeting.manage.signaturesheet.create')
                            </b>
                        @endif

                    </a>
                </li>



                <li class="nav-item">
                    <a href="{{route('secretary.meeting.manage.minutes.create',\Instantiation::instance())}}" class="nav-link">


                        @if(Route::currentRouteName() == 'secretary.meeting.manage.minutes.create'
or Route::currentRouteName() == 'secretary.meeting.manage.minutes.create.step1'
or Route::currentRouteName() == 'secretary.meeting.manage.minutes.create.step2'
or Route::currentRouteName() == 'secretary.meeting.manage.minutes.create.step3')
                            <b>
                        @endif

                            <i class="fas fa-scroll"></i>&nbsp;&nbsp;Crear acta

                        @if(Route::currentRouteName() == 'secretary.meeting.manage.minutes.create'
or Route::currentRouteName() == 'secretary.meeting.manage.minutes.create.step1'
or Route::currentRouteName() == 'secretary.meeting.manage.minutes.create.step2'
or Route::currentRouteName() == 'secretary.meeting.manage.minutes.create.step3')
                            </b>
                        @endif

                    </a>
                </li>

            </ul>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title">Consultar</h3>
        </div>
        <div class="card-body p-0">
            <ul class="nav nav-pills flex-column">

                <li class="nav-item">
                    <a href="{{route('secretary.meeting.manage.request.list',\Instantiation::instance())}}" class="nav-link">


                        @if(Route::currentRouteName() == 'secretary.meeting.manage.request.list')
                            <b>
                        @endif

                                <i class="fas fa-list"></i>&nbsp;&nbsp;Mis convocatorias

                        @if(Route::currentRouteName() == 'secretary.meeting.manage.request.list')
                            </b>
                        @endif

                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{route('secretary.meeting.manage.signaturesheet.list',\Instantiation::instance())}}" class="nav-link">


                        @if(Route::currentRouteName() == 'secretary.meeting.manage.signaturesheet.list'
or Route::currentRouteName() == 'secretary.meeting.manage.signaturesheet.view')
                            <b>
                        @endif

                                <i class="fas fa-list"></i>&nbsp;&nbsp;Mis hojas de firmas

                        @if(Route::currentRouteName() == 'secretary.meeting.manage.signaturesheet.list'
or Route::currentRouteName() == 'secretary.meeting.manage.signaturesheet.view')
                            </b>
                        @endif

                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{route('secretary.meeting.manage.minutes.list',\Instantiation::instance())}}" class="nav-link">


                        @if(Route::currentRouteName() == 'secretary.meeting.manage.minutes.list'
or Route::currentRouteName() == 'secretary.meeting.manage.minutes.edit')
                            <b>
                        @endif

                            <i class="fas fa-list"></i>&nbsp;&nbsp;Mis actas

                        @if(Route::currentRouteName() == 'secretary.meeting.manage.minutes.list'
or Route::currentRouteName() == 'secretary.meeting.manage.minutes.edit')
                            </b>
                        @endif

                    </a>
                </li>

            </ul>
        </div>
    </div>

</div>
