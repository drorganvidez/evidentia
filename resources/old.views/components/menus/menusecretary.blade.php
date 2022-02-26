@if(\Illuminate\Support\Facades\Auth::user()->hasRole('SECRETARY'))

    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <li class="nav-header">REUNIONES Y BONOS</li>

            <x-li route="secretary.meeting.manage" icon='far fa-handshake' name="Gestionar reuniones" secondaries="secretary.meeting.manage.request.create,secretary.meeting.manage.request.list,secretary.meeting.manage.signaturesheet.create,secretary.meeting.manage.signaturesheet.list,secretary.meeting.manage.minutes.list,secretary.meeting.manage.minutes.create,secretary.meeting.manage.minutes.create.step1,secretary.meeting.manage.minutes.create.step2,secretary.meeting.manage.minutes.create.step3,secretary.meeting.manage.minutes.edit,secretary.meeting.manage.signaturesheet.view,secretary.meeting.manage.request.edit,secretary.meeting.manage.signaturesheet.edit"/>
            <x-li route="secretary.bonus.list" secondaries="secretary.bonus.create,secretary.bonus.edit" icon='fas fa-puzzle-piece' name="Gestionar bonos"/>
            <x-li route="secretary.defaultlist.list" secondaries="secretary.defaultlist.create,secretary.defaultlist.edit" icon='far fa-list-alt' name="Gestionar listas"/>

        </ul>
    </nav>
@endif
