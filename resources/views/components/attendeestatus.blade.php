@if ($attendee->status == 'Attending')
    <span class="badge badge-light">Pendiente de asistir</span>
@endif

@if ($attendee->status == 'Checked In')
    <span class="badge badge-success">Asistido</span>
@endif

@if ($attendee->status == 'Guests Attended')
    <span class="badge badge-success">Asistido (invitado)</span>
@endif

@if ($attendee->status == 'Guests Attending')
    <span class="badge badge-light">Pendiente de asistir (invitado)</span>
@endif

@if ($attendee->status == 'Not Attending (Refunded/Canceled)')
    <span class="badge badge-danger">Asistencia cancelada</span>
@endif

@if ($attendee->status == 'Not Attending')
    <span class="badge badge-danger">No asistido</span>
@endif
