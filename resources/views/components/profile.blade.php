<div class="card card-widget shadow-sm">
    <div class="bg-info text-white text-center py-4 rounded-top">
        <h4 class="mb-2">{{ $user->name }} {{ $user->surname }}</h4>
        @foreach($user->roles as $role)
            <span class="badge bg-light text-info px-3 py-1" style="font-size: 0.85rem;">
                {{ $role->slug }}
            </span>
        @endforeach
    </div>
    <div class="card-body text-center">
        <h1 class="display-2 fw-bold mb-2">{{ $user->total_computed_hours() }}</h1>
        <p class="text-uppercase fw-semibold mb-4" style="letter-spacing: 0.15em;">
            horas computadas
        </p>
        <div class="d-flex justify-content-between text-center small text-secondary px-2">
            <div class="flex-fill">
                <div class="fw-bold fs-5">{{ $user->evidences_accepted_hours() }}</div>
                <div style="white-space: nowrap;">en evidencias</div>
            </div>
            <div class="flex-fill">
                <div class="fw-bold fs-5">{{ $user->meetings_hours() }}</div>
                <div style="white-space: nowrap;">en reuniones</div>
            </div>
            <div class="flex-fill">
                <div class="fw-bold fs-5">{{ $user->events_hours() }}</div>
                <div style="white-space: nowrap;">en eventos</div>
            </div>
            <div class="flex-fill">
                <div class="fw-bold fs-5">{{ $user->bonus_hours() }}</div>
                <div style="white-space: nowrap;">bonificadas</div>
            </div>
        </div>
    </div>
</div>


<div class="card card-widget widget-user shadow-sm">
    <div class="widget-user-header position-relative" 
         style="background: url({{ asset('dist/img/trabajo.png') }}) center center / cover no-repeat; min-height: 130px;">

        {{-- Overlay negro semitransparente --}}
        <div style="position: absolute; inset: 0; background-color: rgba(0,0,0,0.6); z-index: 0;"></div>

        <h5 class="widget-user-desc text-left text-white position-relative mb-0 pt-4 px-3" 
            style="font-weight: 600; font-size: 1.3rem; z-index: 1;">
            <i class="fas fa-stopwatch mr-2"></i>
            <i class="fas fa-briefcase mr-2"></i>
            Resumen de trabajo realizado en las Jornadas Innosoft Days {{ \Carbon\Carbon::now()->year }}
        </h5>
    </div>

    <div class="card-footer pt-3">
        <div class="row">
            <div class="col-lg-12">

            @if($user->participation_label)
                <span class="badge bg-info text-dark">
                    {{ $user->participation_label }}
                </span>
            @endif


                <p style="text-align: justify; margin-bottom: 0;" class="biography">
                    @if(!$user->biography)
                        <div class="callout callout-danger mt-3">
                            <h5>Ups...</h5>
                            <p>
                                Parece que no has rellenado este apartado. ¡No lo olvides! Indica con detalle tu nivel
                                de implicación en las jornadas desde <a href="{{ route('profile.view') }}">Mi perfil</a>.
                            </p>
                        </div>
                    @endif

                    {!! $user->biography !!}
                </p>
            </div>
        </div>
    </div>
</div>

