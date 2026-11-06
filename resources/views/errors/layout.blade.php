{{--
    Error pages with the public layout: a big code, one sentence and the way back. The 500 page adds the reference the
    error recorder kept, so the person can tell it and the lecturer finds the error by it. With an icon the code gives
    way to it; action back adds «Volver a intentarlo» to the way home and action retry leaves only that button, for the
    maintenance page, where the home page is closed too.
    @include('errors.layout', ['code' => '404', 'title' => 'No encontramos esa página', 'text' => '...'])
--}}
@php
    // The labels live here: blade-formatter would cut them inside the button tags
    $action ??= null;
    $retry = 'Volver a intentarlo';
    $home = 'Volver al inicio';
@endphp
<x-layout.public :title="$title" content-id="error" skip-label="Saltar al contenido" centered>
    <x-ui.card as="div" class="w-full">
        <x-ui.empty :code="isset($icon) ? null : $code" :icon="$icon ?? 'inbox'" :title="$title" :text="$text ?? null" :level="1"
            size="lg">
            <x-slot:actions>
                <div class="grid justify-items-center gap-5 pt-3">
                    @isset($reference)
                        <p class="flex flex-wrap items-center justify-center gap-2 text-sm">
                            Código de referencia
                            <x-ui.badge variant="chip" mono>{{ $reference }}</x-ui.badge>
                        </p>
                    @endisset
                    <div class="flex flex-wrap justify-center gap-3">
                        @if ($action === 'retry')
                            <x-ui.button variant="primary" icon="rotate-ccw"
                                :href="url()->current()">{{ $retry }}</x-ui.button>
                        @else
                            @if ($action === 'back')
                                <x-ui.button variant="primary" icon="rotate-ccw"
                                    :href="url()->previous()">{{ $retry }}</x-ui.button>
                            @endif
                            <x-ui.button :variant="$action === 'back' ? 'quiet' : 'primary'" icon="arrow-left"
                                :href="url('/')">{{ $home }}</x-ui.button>
                        @endif
                    </div>
                </div>
            </x-slot:actions>
        </x-ui.empty>
    </x-ui.card>
</x-layout.public>
