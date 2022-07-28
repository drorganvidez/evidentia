<x-sub-menu>
    <x-slot:subitems>
        Nueva evidencia, evidences.create;
        En borrador, evidences.draft, {{Auth::user()->evidences_draft_count()}};
        Pendientes, evidences.pending, {{Auth::user()->evidences_pending_count()}}, warning;
        Aceptadas, evidences.accepted, {{Auth::user()->evidences_accepted_count()}}, success;
        Rechazadas, evidences.rejected, {{Auth::user()->evidences_rejected_count()}}, danger
    </x-slot:subitems>
</x-sub-menu>