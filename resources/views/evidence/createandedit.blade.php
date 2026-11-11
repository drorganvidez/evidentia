{{--
    Form of a new evidence and of the edit of a draft or a rejected one. The same form saves a draft (its action) or
    publishes (the formaction of the confirmation); the proofs travel to the temporary folder of the session through
    the upload endpoints, where the controller picks them up. The description is plain text: the HTML of the old
    editor reaches the textarea as its text.
--}}
@php
    $editing = isset($edit);
    $title = $editing ? 'Editar evidencia' : 'Nueva evidencia';
    $closesAt = \Config::upload_evidences_timestamp();
    $reasons = $editing ? $evidence->allRejectionReasons() : collect();
    $committeeOptions = $committees->mapWithKeys(fn($committee) => [$committee->id => $committee->name])->all();
    $breadcrumbs = [
        ['label' => 'Inicio', 'href' => route('home')],
        ['label' => 'Mis evidencias', 'href' => route('evidence.list')],
    ];
    if ($editing) {
        $breadcrumbs[] = ['label' => $evidence->title, 'href' => route('evidence.view', ['id' => $evidence->id])];
    }
    $breadcrumbs[] = ['label' => $title];
@endphp
<x-layout.app :title="$title" :breadcrumbs="$breadcrumbs" :nav-current="$editing ? 'evidence.list' : 'evidence.create'">
    <x-ui.page-header :title="$title">
        @if ($closesAt !== null)
            <x-slot:meta>
                <x-ui.deadline label="Subida de evidencias" variant="inline" verb="para enviarla" :closes-at="$closesAt" />
            </x-slot:meta>
        @endif
    </x-ui.page-header>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)] lg:gap-8">
        <x-ui.card :title="$editing ? $evidence->title : 'Datos de la evidencia'" :level="2" id="evidence-card">
            <x-ui.form :action="$route_draft" files id="evidence-form" busy-label="Guardando" :fields="[
                'title' => 'Título',
                'hours' => 'Horas',
                'minutes' => 'Minutos',
                'committee' => 'Comité',
                'coauthors' => 'Coautores',
                'description' => 'Descripción',
            ]">
                @if ($editing)
                    <input type="hidden" name="_id" value="{{ $evidence->id }}">
                @endif
                <input type="hidden" name="removed_files" id="removed_files">

                <x-ui.field name="title" label="Título" :value="$evidence->title ?? ($prefill['title'] ?? null)" required hint="Al menos 5 caracteres" />

                <fieldset class="grid gap-1.5">
                    <legend class="mb-1.5 text-sm font-semibold">Duración</legend>
                    <div class="grid grid-cols-2 gap-3">
                        <x-ui.field name="hours" label="Horas" type="number" suffix="h" inputmode="decimal"
                            min="0" max="99" step="0.01" :value="$prefill['hours'] ?? \Time::complex_shape_hours($evidence->hours ?? '')" />
                        <x-ui.field name="minutes" label="Minutos" type="number" suffix="min" inputmode="numeric"
                            min="0" max="60" :value="$prefill['minutes'] ?? \Time::complex_shape_minutes($evidence->hours ?? '')" />
                    </div>
                </fieldset>

                <x-ui.field name="committee" label="Comité" type="select" :options="$committeeOptions" :value="$evidence->committee_id ?? null"
                    required />

                <x-ui.picker name="coauthors[]" label="Coautores" :options="$students" :selected="$editing ? $evidence->coauthors : []"
                    placeholder="Buscar compañeros por nombre o apellidos"
                    hint="Si hiciste el trabajo con otros alumnos, añádelos y se les computarán las mismas horas cuando se acepte" />

                <x-ui.field name="description" label="Descripción" type="textarea" :rows="8" :maxlength="20000"
                    :value="$editing ? \App\View\PlainText::fromHtml($evidence->description) : null" hint="Al menos 10 caracteres" required />

                <x-ui.upload name="files[]" :url="route('upload.process')" :revert-url="route('upload.revert')" :remove-url="route('upload.remove', ['file_name' => 'NAME'])" :files="Filepond::getFilesFromTemporaryFolder()->all()"
                    label="Arrastra aquí las pruebas o pulsa para elegirlas" />

                <x-slot:actions>
                    <x-ui.dialog id="publish" variant="alert" title="Publicar la evidencia"
                        description="Una vez publicada no podrás editarla mientras se revisa." trigger="Publicar"
                        trigger-icon="send" trigger-variant="primary">
                        <x-slot:footer>
                            <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
                            <x-ui.button variant="primary" type="submit" icon="send"
                                formaction="{{ $route_publish }}">Sí, publicar</x-ui.button>
                        </x-slot:footer>
                    </x-ui.dialog>
                    <x-ui.button type="submit" icon="save">Guardar borrador</x-ui.button>
                </x-slot:actions>
            </x-ui.form>
        </x-ui.card>

        @if ($editing)
            <aside aria-label="Rechazos y ediciones" class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
                @if ($reasons->isNotEmpty())
                    <x-ui.alert tone="rejected" title="Motivos del rechazo">
                        <ul class="grid gap-2">
                            @foreach ($reasons->reverse() as $rejection)
                                <li>
                                    {{ $rejection->reason }}
                                    <x-ui.time :value="$rejection->created_at" format="absolute" class="block text-xs" />
                                </li>
                            @endforeach
                        </ul>
                    </x-ui.alert>
                @endif

                <x-ui.card title="Ediciones" :level="2" id="versions">
                    <x-ui.timeline label="Ediciones de la evidencia">
                        @foreach ($evidence->flowEvidences() as $version)
                            @php
                                $current = (int) request()->route('id') === $version->id;
                            @endphp
                            <x-ui.timeline.item :tone="$current ? 'notice' : 'draft'" :title="$current ? 'Editando ahora' : $version->status_label" :at="$version->created_at">
                                @if ($current)
                                    <span class="block">{{ $version->title }}</span>
                                @else
                                    <a href="{{ route('evidence.edit', ['id' => $version->id]) }}"
                                        class="block font-medium text-brand-ink">{{ $version->title }}</a>
                                @endif
                            </x-ui.timeline.item>
                        @endforeach
                    </x-ui.timeline>
                </x-ui.card>
            </aside>
        @endif
    </div>
</x-layout.app>
