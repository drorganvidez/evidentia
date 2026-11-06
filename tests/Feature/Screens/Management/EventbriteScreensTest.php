<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Attendee;
use App\Models\Configuration;
use App\Models\Event;
use Carbon\Carbon;

/** The screens of the register coordinator: the token, the events and the attendances. */
class EventbriteScreensTest extends ScreenTestCase
{
    public function test_the_token_form(): void
    {
        Configuration::query()->update(['eventbrite_token' => 'TOKEN123']);

        $this->screen('registro', route('registercoordinator.token'))
            ->assertSee('action="'.route('registercoordinator.token.save').'"', false)
            ->assertSee('name="token"', false)
            ->assertSee('value="TOKEN123"', false);
    }

    public function test_the_events_with_the_actions_of_the_hand_made_ones(): void
    {
        $event = Event::where('name', 'Taller de Git')->sole();
        $imported = Event::forceCreate([
            'id_eventbrite' => '998877', 'name' => '<p>Charla &amp; café</p>', 'url' => 'https://www.eventbrite.es/e/998877',
            'start_datetime' => now()->subDay(), 'end_datetime' => now()->subDay()->addHour(), 'hours' => 1,
            'status' => 'completed', 'hidden' => true,
        ]);

        $this->screen('registro', route('registercoordinator.event.list'))
            ->assertSee('Taller de Git')
            ->assertSee('Creado a mano')
            ->assertSee('Charla &amp; café', false)
            ->assertSee('Oculto')
            ->assertSee('href="'.route('registercoordinator.event.edit', $event->id).'"', false)
            ->assertSee('action="'.route('registercoordinator.event.destroy', $event->id).'"', false)
            ->assertSee('href="'.route('registercoordinator.attendee.load', ['id' => '998877']).'"', false)
            ->assertSee('action="'.route('registercoordinator.event.unhide', ['id' => $imported->id]).'"', false)
            ->assertDontSee('action="'.route('registercoordinator.event.destroy', $imported->id).'"', false);
    }

    public function test_once_the_deadline_closes_the_events_are_read_only(): void
    {
        Configuration::query()->update(['attendee_timestamp' => now()->subDay()]);

        $this->screen('registro', route('registercoordinator.event.list'))
            ->assertSee('Taller de Git')
            ->assertDontSee(route('registercoordinator.event.create'))
            ->assertDontSee('/registercoordinator/event/hide/', false);

        $this->screen('registro', route('registercoordinator.attendee.list'))
            ->assertSee('Pérez Romero, Lucía')
            ->assertSee('href="'.route('registercoordinator.attendee.export').'"', false)
            ->assertDontSee('/attendees/create', false);
    }

    public function test_the_event_form_creates_and_edits(): void
    {
        $event = Event::where('name', 'Taller de Git')->sole();

        $this->screen('registro', route('registercoordinator.event.create'))
            ->assertSee('Nuevo evento')
            ->assertSee('action="'.route('registercoordinator.event.store').'"', false)
            ->assertSee('name="start_datetime"', false);

        $this->screen('registro', route('registercoordinator.event.edit', $event->id))
            ->assertSee('Editar evento')
            ->assertSee('action="'.route('registercoordinator.event.update', $event->id).'"', false)
            ->assertSee('value="Taller de Git"', false)
            ->assertSee('value="'.Carbon::parse($event->start_datetime)->format('Y-m-d\TH:i').'"', false);
    }

    public function test_the_attendances_and_their_forms(): void
    {
        $attendee = Attendee::where('user_id', $this->person('estudiante')->id)->sole();
        $event = Event::where('name', 'Taller de Git')->sole();

        $this->screen('registro', route('registercoordinator.attendee.list'))
            ->assertSee('Pérez Romero, Lucía')
            ->assertSee('Taller de Git')
            ->assertSee('Asistido')
            ->assertSee('href="'.route('attendee.edit', $attendee->id).'"', false)
            ->assertSee('href="'.route('attendee.create').'"', false);

        $this->screen('registro', route('attendee.create', ['event' => $event->id]))
            ->assertSee('action="'.route('attendee.store').'"', false)
            ->assertSee('<option value="'.$event->id.'" selected', false);

        $this->screen('registro', route('attendee.edit', $attendee->id))
            ->assertSee('action="'.route('attendee.update', $attendee->id).'"', false)
            ->assertSee('<option value="'.$this->person('estudiante')->id.'" selected', false)
            ->assertSee('<option value="Checked In" selected', false)
            ->assertSee('action="'.route('attendee.destroy', $attendee->id).'"', false);
    }
}
