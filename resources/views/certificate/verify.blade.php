{{-- Public: whether a code printed on a certificate or a diploma belongs to one issued by Evidentia --}}
<x-layout.public title="Verificar un certificado">
    <x-ui.card title="Verificar un certificado" :level="1">
        <div class="grid gap-5">
            @if ($code !== '')
                @if ($certificate)
                    <x-ui.alert tone="accepted" role="status" title="Certificado válido">
                        El código {{ $certificate->code }} corresponde a un documento emitido por Evidentia.
                    </x-ui.alert>
                    <x-ui.dl>
                        <x-ui.dl.item term="Documento">{{ $certificate->typeLabel() }}</x-ui.dl.item>
                        <x-ui.dl.item term="Persona">{{ $certificate->recipient_name }}</x-ui.dl.item>
                        <x-ui.dl.item :term="$certificate->isDiploma() ? 'Motivo' : 'Evento'">{{ $certificate->title }}</x-ui.dl.item>
                        @if ($certificate->hours)
                            <x-ui.dl.item term="Horas"><x-ui.hours :value="$certificate->hours" /></x-ui.dl.item>
                        @endif
                        <x-ui.dl.item term="Emitido"><x-ui.time :value="$certificate->issued_at" format="absolute" pattern="d/m/Y" /></x-ui.dl.item>
                        <x-ui.dl.item term="Código" mono>{{ $certificate->code }}</x-ui.dl.item>
                    </x-ui.dl>
                @else
                    <x-ui.alert tone="rejected" role="status" title="Código no válido">
                        No hay ningún certificado con el código {{ $code }}. Puede estar mal escrito o haber sido revocado.
                    </x-ui.alert>
                @endif
            @endif
            <form method="get" action="{{ route('certificate.verify') }}" class="grid gap-4">
                <x-ui.field name="code" label="Código de verificación" :value="$code" hint="Aparece al pie del PDF, por ejemplo ABCD-EFGH-JKMN"
                    autocomplete="off" required />
                <div><x-ui.button type="submit" variant="primary" icon="search">Verificar</x-ui.button></div>
            </form>
        </div>
    </x-ui.card>
</x-layout.public>
