<div class="col-lg-4">

<div class="card">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Crear elementos de reunión</h5>
    </div>
    <div class="card-body p-0">
        <ul class="nav nav-pills flex-column">

            <li class="nav-item">
                <a href="{{ route('secretary.meeting.manage.request.create') }}"
                   class="nav-link {{ Route::currentRouteName() == 'secretary.meeting.manage.request.create' ? 'active' : '' }}">
                    <i class="fas fa-child me-2"></i> Crear convocatoria
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('secretary.meeting.manage.signaturesheet.create') }}"
                   class="nav-link {{ Route::currentRouteName() == 'secretary.meeting.manage.signaturesheet.create' ? 'active' : '' }}">
                    <i class="fas fa-signature me-2"></i> Crear hoja de firmas
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('secretary.meeting.manage.minutes.create') }}"
                   class="nav-link
                       {{ in_array(Route::currentRouteName(), [
                            'secretary.meeting.manage.minutes.create',
                            'secretary.meeting.manage.minutes.create.step1',
                            'secretary.meeting.manage.minutes.create.step2',
                            'secretary.meeting.manage.minutes.create.step3'
                        ]) ? 'active' : '' }}">
                    <i class="fas fa-scroll me-2"></i> Crear acta
                </a>
            </li>

        </ul>
    </div>
</div>


 <div class="card">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-search me-2"></i> Consultar elementos de reunión</h5>
    </div>
    <div class="card-body p-0">
        <ul class="nav nav-pills flex-column">

            <li class="nav-item">
                <a href="{{ route('secretary.meeting.manage.request.list') }}"
                   class="nav-link
                       {{ in_array(Route::currentRouteName(), [
                            'secretary.meeting.manage.request.list',
                            'secretary.meeting.manage.request.edit'
                        ]) ? 'active' : '' }}">
                    <i class="fas fa-list me-2"></i> Mis convocatorias
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('secretary.meeting.manage.signaturesheet.list') }}"
                   class="nav-link
                       {{ in_array(Route::currentRouteName(), [
                            'secretary.meeting.manage.signaturesheet.list',
                            'secretary.meeting.manage.signaturesheet.view',
                            'secretary.meeting.manage.signaturesheet.edit'
                        ]) ? 'active' : '' }}">
                    <i class="fas fa-signature me-2"></i> Mis hojas de firmas
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('secretary.meeting.manage.minutes.list') }}"
                   class="nav-link
                       {{ in_array(Route::currentRouteName(), [
                            'secretary.meeting.manage.minutes.list',
                            'secretary.meeting.manage.minutes.edit'
                        ]) ? 'active' : '' }}">
                    <i class="fas fa-scroll me-2"></i> Mis actas
                </a>
            </li>

        </ul>
    </div>
</div>


</div>
