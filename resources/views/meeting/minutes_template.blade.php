
<style>

#body{
    font-family: 'Helvetica'
}

table, th, td {
    border:1px solid black;
    border-collapse: collapse;
}

th, td {
    padding: 5px;
}

table{
    width: 100%;
}

.header_tr{
    background-color: #1E2949;
    color: white;
    font-size: 20px;
}

.X{
    text-align: center;
}

</style>

<div style="margin: 50px 50px 0px 50px" id="body">

    <div style="text-align: center">

        <img src="dist/img/innosoft.png" width="100px"/>

        <h1 style="color: #1E2949">
            JORNADAS INNOSOFT DAYS {{\Carbon\Carbon::now()->format('Y')}}
        </h1>

        <h2 style="color: #79AED3">
            ACTA DE REUNIÓN
        </h2>

    </div>

    <table>

        <tr class="header_tr">
            <th colspan="2">{{$meeting_minutes->meeting->title}}</th>
        </tr>

        <tr>
            <td>Fecha</td>
            <td>{{\Carbon\Carbon::now()->format('d/m/Y')}}</td>
        </tr>

        <tr>
            <td>Hora comienzo</td>
            <td>{{ \Carbon\Carbon::parse($meeting_minutes->meeting->datetime)->format('H:i') }}</td>
        </tr>

        <tr>
            <td>Hora fin</td>
            <td>{{ \Carbon\Carbon::parse($meeting_minutes->meeting->datetime)->addHours($meeting_minutes->meeting->hours)->format('H:i') }}</td>
        </tr>

        <tr>
            <td>Lugar</td>
            <td>{{ $meeting_minutes->meeting->place }}</td>
        </tr>

        <tr>
            <td>Convoca</td>
            <td>{{$meeting_minutes->meeting->meeting_request->secretary->user->name}} {{$meeting_minutes->meeting->meeting_request->secretary->user->surname}}</td>
        </tr>

        <tr>
            <td>Comité</td>
            <td>{{$meeting_minutes->meeting->comittee->name}}</td>
        </tr>

        <tr>
            <td>Tipo de reunión</td>
            <td>
                @switch($meeting_minutes->meeting->type)
                    @case('ORDINARY')
                    Ordinaria
                    @break
                    @case('EXTRAORDINARY')
                    Extraordinaria
                    @break
                @endswitch
            </td>
        </tr>

        <tr>
            <td>Modalidad</td>
            <td>
                @switch($meeting_minutes->meeting->modality)
                    @case('F2F')
                    Presencial
                    @break
                    @case('TELEMATIC')
                    Telemática
                    @break
                    @case('MIXED')
                    Híbrida
                    @break
                @endswitch
            </td>
        </tr>

    </table>

    <br>

    <table>

        <tr class="header_tr">
            <th>Rol</th>
            <th>Apellidos</th>
            <th>Nombre</th>
            <th>Asistencia</th>
        </tr>

        @foreach($meeting_minutes->meeting->comittee->coordinators as $coordinator)
            <tr>
                @if ($loop->first)
                    <td class="X" rowspan="{{$meeting_minutes->meeting->comittee->coordinators->count()}}">
                        COORDINADORES
                    </td>
                @endif>

                <td>
                    {{$coordinator->user->surname}}
                </td>
                <td>
                    {{$coordinator->user->name}}
                </td>
                <td class="X">
                    @if($meeting_minutes->meeting->users->contains($coordinator->user))
                        X
                    @endif
                </td>
            </tr>
        @endforeach

        @foreach($meeting_minutes->meeting->comittee->secretaries as $secretary)
            <tr>
                @if ($loop->first)
                    <td class="X" rowspan="{{$meeting_minutes->meeting->comittee->secretaries->count()}}">
                        SECRETARIOS
                    </td>
                @endif>

                <td>
                    {{$secretary->user->surname}}
                </td>
                <td>
                    {{$secretary->user->name}}
                </td>
                <td class="X">
                    X
                </td>
            </tr>
        @endforeach


        @foreach($meeting_minutes->meeting->users as $user)
            <tr>
                @if ($loop->first)
                    <td class="X" rowspan="{{$meeting_minutes->meeting->users->count()}}">
                        ASISTENTES
                    </td>
                @endif>

                <td>
                    {{$user->surname}}
                </td>
                <td>
                    {{$user->name}}
                </td>
                <td class="X">
                    X
                </td>
            </tr>
        @endforeach

    </table>

    <h3 style="margin-top: 50px; color: #79AED3">Orden del día</h3>

    <ol>
        @foreach($meeting_minutes->points as $point)
            <li>
                {{$point->title}}
            </li>

        @endforeach
    </ol>

    <h3 style="margin-top: 50px; color: #79AED3">Desarrollo de la reunión</h3>

    <h3 style="margin-top: 50px; color: #79AED3">Cierre de la reunión</h3>

    <p>
        Se levantó la sesión de la reunión a las {{ \Carbon\Carbon::parse($meeting_minutes->meeting->datetime)->addHours($meeting_minutes->meeting->hours)->format('H:i') }}
        del día 27 de Octubre de 2020 por {{$meeting_minutes->meeting->meeting_request->secretary->user->name}} {{$meeting_minutes->meeting->meeting_request->secretary->user->surname}}
    </p>

    <div style="text-align: center">
        <p>
            En conformidad con lo anterior dispuesto y para que conste en acta, da fe:
        </p>

        <h4>
            {{$meeting_minutes->meeting->meeting_request->secretary->user->name}} {{$meeting_minutes->meeting->meeting_request->secretary->user->surname}}
        </h4>

        <h3>
            Secretario de {{$meeting_minutes->meeting->comittee->name}}
        </h3>
    </div>



</div>
