
<style>

#body{
    font-family: 'Helvetica'
}

</style>

<div style="margin: 50px 50px 0px 50px" id="body">

    <div style="text-align: center">

        <img src="dist/img/innosoft.png" width="100px"/>

        <h1 style="color: #1E2949">
            JORNADAS INNOSOFT DAYS {{\Carbon\Carbon::now()->format('Y')}}
        </h1>

        <h2 style="color: #79AED3">
            CONVOCATORIA DE REUNIÓN
        </h2>

        <h3 style="color: #79AED3">
            Comité de {{$meeting_request->committee->name}}
        </h3>

        <h3>
            "{{$meeting_request->title}}"
        </h3>

        <p style="margin-top: 20px">

            Por medio del presente escrito, queda usted convocado/a a la reunión<b>
            @switch($meeting_request->type)
                @case('ORDINARY')
                ordinaria
                @break
                @case('EXTRAORDINARY')
                extraordinaria
                @break
            @endswitch
            </b> con modalidad<b>
            @switch($meeting_request->modality)
                @case('F2F')
                presencial
                @break
                @case('TELEMATIC')
                telemática
                @break
                @case('MIXED')
                híbrida
                @break
            @endswitch
            </b> del comité de <b>{{$meeting_request->committee->name}}</b>, que se celebrará el día <b>{{ \Carbon\Carbon::parse($meeting_request->datetime)->format('d/m/Y') }}</b>
            a las <b>{{ \Carbon\Carbon::parse($meeting_request->datetime)->format('H:i') }}</b> en <b>{{$meeting_request->place}}</b>, en la que
            se tratarán los asuntos que se expresan a continuación:
        </p>

    </div>

    <h3 style="margin-top: 50px; color: #79AED3">Orden del día</h3>

    <ol>
        @foreach($meeting_request->diary->diaryPoints as $point)
            <li>
                {{$point->point}}
            </li>

        @endforeach
    </ol>

    <div style="text-align: center">


        <h3 style="margin-top: 50px">
            Le saluda cordialmente
        </h3>

        <h4>
            {{$meeting_request->secretary->user->name}} {{$meeting_request->secretary->user->surname}}
        </h4>

        <h3>
            Secretaría {{$meeting_request->committee->name}}
        </h3>
    </div>

</div>
