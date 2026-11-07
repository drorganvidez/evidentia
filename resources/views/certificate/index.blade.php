{{-- Every certificate issued: the diplomas of the management and the certificates of attendance the students download --}}
@php
    $tabs = [
        ['label' => 'Todos', 'href' => route('certificate.index'), 'current' => $type === null],
        ['label' => 'Diplomas', 'href' => route('certificate.index', ['tipo' => 'diplomas']), 'current' => $type === 'diplomas'],
        ['label' => 'Asistencia', 'href' => route('certificate.index', ['tipo' => 'asistencia']), 'current' => $type === 'asistencia'],
    ];
@endphp
<x-layout.app title="Certificados y diplomas" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Certificados y diplomas']]">
    <x-ui.page-header title="Certificados y diplomas"
        lede="Cada documento lleva un código que cualquiera comprueba en la página pública de verificación. Revocar uno invalida su código.">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="plus" :href="route('certificate.create')" id="certificate-create">Emitir diploma</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table caption="Certificados emitidos" :columns="[
        ['label' => 'Persona'],
        ['label' => 'Documento'],
        ['label' => 'Código'],
        ['label' => 'Emitido'],
        ['label' => 'Acciones', 'srOnly' => true],
    ]" :paginator="$certificates" filterable
        filter-placeholder="Buscar certificados" count-label="certificados" count-singular="certificado">
        <x-slot:toolbar>
            <x-ui.tabs label="Tipo" :items="$tabs" />
        </x-slot:toolbar>
        @foreach ($certificates as $certificate)
            <x-ui.table.row>
                <x-ui.table.cell label="Persona" primary>
                    {{ $certificate->recipient_name }}
                    <span class="mt-0.5 block text-sm text-ink-muted">{{ $certificate->title }}</span>
                </x-ui.table.cell>
                <x-ui.table.cell label="Documento">
                    <x-ui.badge :tone="$certificate->isDiploma() ? 'accepted' : 'notice'" size="sm">{{ $certificate->typeLabel() }}</x-ui.badge>
                </x-ui.table.cell>
                <x-ui.table.cell label="Código" meta><x-ui.badge variant="chip" mono>{{ $certificate->code }}</x-ui.badge></x-ui.table.cell>
                <x-ui.table.cell label="Emitido" meta>
                    <x-ui.time :value="$certificate->issued_at" format="absolute" pattern="d/m/Y" />
                </x-ui.table.cell>
                <x-ui.table.cell tools>
                    <div class="flex items-center justify-end gap-1">
                        <x-ui.button size="sm" variant="ghost" icon="download" icon-only :label="'Descargar ' . $certificate->code"
                            :href="route('certificate.download', $certificate->code)" />
                        <x-ui.button size="sm" variant="ghost" icon="shield-check" icon-only :label="'Verificar ' . $certificate->code"
                            :href="$certificate->verifyUrl()" />
                        <x-ui.dialog :id="'revoke-certificate-' . $certificate->id" variant="alert" title="Revocar el certificado"
                            :description="'El código ' . $certificate->code . ' dejará de ser válido. Esta acción no se puede deshacer.'" :trigger="'Revocar ' . $certificate->code"
                            trigger-variant="ghost" trigger-icon="trash-2" trigger-icon-only>
                            <x-slot:footer>
                                <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
                                <form method="post" action="{{ route('certificate.destroy', $certificate->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button variant="danger" type="submit" icon="trash-2" block>Sí, revocar</x-ui.button>
                                </form>
                            </x-slot:footer>
                        </x-ui.dialog>
                    </div>
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
        @if ($certificates->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="award" :title="request('q') ? 'Ningún certificado coincide' : 'Aún no hay certificados'" size="sm" :level="2" />
            </x-slot:empty>
        @endif
    </x-ui.table>
</x-layout.app>
