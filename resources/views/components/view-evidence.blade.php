<div class="card card-body pb-2">

    <div class="row">

    </div>

    <div class="row">
        <div class="col-8 ">
            <p class="text-muted mb-0 pb-0">
                #{{$evidence->id}} - {{$evidence->user->full_name()}}
            </p>

            <!-- Title -->
            <h2 class="mb-2 mt-0 pb-0">
                {{$evidence->title}}
            </h2>

            <!-- Text -->
            <p class="text-muted mb-4">
                Última actualización:
                {{ \Carbon\Carbon::parse($evidence->updated_at)->format('d/m/Y H:i')}}
            </p>

        </div>

        <div class="col-4 text-end">

            <div class="badge bg-{{$evidence->color_status()}} mb-2">
                {{$evidence->spanish_status()}}
            </div>

            <div class="badge bg-primary-soft mb-2">
                {{$evidence->committee->name}}
            </div>

        </div>

    </div>

    @if($evidence->guest != null)
        <div class="row">

            <div class="col-6col-md-6">

                <h6 class="text-muted text-uppercase">
                    Tiempo empleado
                </h6>

                <p class="mb-4">
                    {{ $evidence->full_time() }}
                </p>
            </div>

            <div class="col-6 col-md-6">

                <h6 class="text-muted text-uppercase">
                    Estudiante asociado
                </h6>

                <p class="mb-4">
                    {{ $evidence->guest->surname }}, {{ $evidence->guest->name }}
                </p>
            </div>

        </div>

    @endif

    <div class="row">

        <div class="col-12 col-md-12">

            <h6 class="text-muted text-uppercase">
                Descripción
            </h6>

            {!! $evidence->description !!}
        </div>

        @if($evidence->review)

            <div class="col-12 col-md-12">

                <h6 class="text-muted text-uppercase">
                    Revisión
                </h6>

                <div class="badge bg-{{$evidence->color_status()}} mb-2">
                    Puntuación: {{ $evidence->review->score }}
                </div>

                <div class="card bg-{{$evidence->color_status()}}" style="margin-bottom: 17px">

                    <div class="card-body pb-2" style="color: white">

                        <!-- Text -->
                        {!! $evidence->review->comment !!}

                    </div>
                </div>

            </div>

        @endif

    </div>

    {{--

    <div class="row">
        <div class="col-12 col-md-6">

            <!-- Heading -->
            <h6 class="text-uppercase text-muted">
                Invoiced from
            </h6>

            <!-- Text -->
            <p class="text-muted mb-4">
                <strong class="text-body">Kenny Blankenship</strong> <br>
                CEO of Good Themes <br>
                123 Happy Walk Way <br>
                San Francisco, CA
            </p>

            <!-- Heading -->
            <h6 class="text-uppercase text-muted">
                Invoiced ID
            </h6>

            <!-- Text -->
            <p class="mb-4">
                #SDF9823KD
            </p>

        </div>
        <div class="col-12 col-md-6 text-md-end">

            <!-- Heading -->
            <h6 class="text-uppercase text-muted">
                Invoiced to
            </h6>

            <!-- Text -->
            <p class="text-muted mb-4">
                <strong class="text-body">Jimmy LeBuyer</strong> <br>
                Acquisitions at Themers <br>
                236 Main St., #201 <br>
                Los Angeles, CA
            </p>

            <!-- Heading -->
            <h6 class="text-uppercase text-muted">
                Due date
            </h6>

            <!-- Text -->
            <p class="mb-4">
                <time datetime="2018-04-23">Apr 23, 2018</time>
            </p>

        </div>
    </div> <!-- / .row -->
    <div class="row">
        <div class="col-12">

            <!-- Table -->
            <div class="table-responsive">
                <table class="table my-4">
                    <thead>
                    <tr>
                        <th class="px-0 bg-transparent border-top-0">
                            <span class="h6">Description</span>
                        </th>
                        <th class="px-0 bg-transparent border-top-0">
                            <span class="h6">Hours</span>
                        </th>
                        <th class="px-0 bg-transparent border-top-0 text-end">
                            <span class="h6">Cost</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="px-0">
                            Custom theme development
                        </td>
                        <td class="px-0">
                            125
                        </td>
                        <td class="px-0 text-end">
                            $6,250
                        </td>
                    </tr>
                    <tr>
                        <td class="px-0">
                            Logo design
                        </td>
                        <td class="px-0">
                            15
                        </td>
                        <td class="px-0 text-end">
                            $750
                        </td>
                    </tr>
                    <tr>
                        <td class="px-0 border-top border-top-2">
                            <strong>Total amount due</strong>
                        </td>
                        <td colspan="2" class="px-0 text-end border-top border-top-2">
                <span class="h3">
                  $7,000
                </span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <hr class="my-5">

            <!-- Title -->
            <h6 class="text-uppercase">
                Notes
            </h6>

            <!-- Text -->
            <p class="text-muted mb-0">
                We really appreciate your business and if there’s anything else we can do, please let us know! Also, should you need us to add VAT or anything else to this order, it’s super easy since this is a template, so just ask!
            </p>

        </div>
    </div> <!-- / .row -->

    --}}
</div>