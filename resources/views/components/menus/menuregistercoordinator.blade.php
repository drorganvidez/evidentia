@if(\Illuminate\Support\Facades\Auth::user()->hasRole('REGISTER_COORDINATOR'))

    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <li class="nav-header">ASISTENCIAS</li>
            <x-li route="home" icon='fas fa-calendar-minus' name="Ajutes de Eventbrite"/>
            <x-li route="home" icon='fas fa-book-reader' name="Volcar asistencias"/>

        </ul>
    </nav>
@endif

