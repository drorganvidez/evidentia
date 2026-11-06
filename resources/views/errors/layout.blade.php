{{--
    Error pages with the public layout: a big code, one sentence and the way back. The 500 page adds the reference the
    error recorder kept, so the person can tell it and the lecturer finds the error by it.
    @include('errors.layout', ['code' => '404', 'title' => 'No encontramos esa página', 'text' => '...'])
--}}
<x-layout.public :title="$title" content-id="error" skip-label="Saltar al contenido">
    <x-ui.card as="div" class="w-full">
        <x-ui.empty :code="$code" :title="$title" :text="$text ?? null" :level="1">
            @isset($reference)
                <x-slot:actions>
                    <p class="flex flex-wrap items-center justify-center gap-2 text-sm">
                        Código de referencia
                        <x-ui.badge variant="chip" mono>{{ $reference }}</x-ui.badge>
                    </p>
                </x-slot:actions>
            @endisset
        </x-ui.empty>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            @if (($action ?? null) === 'back')
                <x-ui.button variant="primary" icon="rotate-ccw" :href="url()->previous()">Volver a intentarlo</x-ui.button>
            @endif
            <x-ui.button :variant="($action ?? null) === 'back' ? 'quiet' : 'primary'" icon="arrow-left" :href="url('/')">Volver al inicio</x-ui.button>
        </div>
    </x-ui.card>
</x-layout.public>
