<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Coordinator;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use App\View\Navigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    private Committee $committee;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate([
            'upload_evidences_timestamp' => now()->addWeek(),
            'validate_evidences_timestamp' => now()->addWeeks(2),
            'meetings_timestamp' => now()->addWeek(),
            'bonus_timestamp' => now()->addWeek(),
            'attendee_timestamp' => now()->addWeek(),
        ]);
        $this->committee = Committee::create(['name' => 'Logística']);
    }

    private function user(string ...$roles): User
    {
        $user = User::factory()->create();
        foreach ($roles as $role) {
            $user->roles()->attach(Role::where('rol', $role)->first() ?? Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]));
        }

        return $user->fresh();
    }

    /** @return array<string, list<string>> group label => item labels */
    private function labels(array $nav): array
    {
        return collect($nav)->mapWithKeys(fn (array $group) => [
            $group['label'] ?? '' => array_column($group['items'], 'label'),
        ])->all();
    }

    public function test_a_student_sees_their_things_and_a_bar_of_five(): void
    {
        $menu = app(Navigation::class)->for($this->user('STUDENT'), 'evidence.view');

        $this->assertSame([
            '' => ['Inicio', 'Mi perfil', 'Mis mensajes', 'Mi actividad', 'Buscar acuerdos'],
            'Mis cosas' => ['Nueva evidencia', 'Mis evidencias', 'Mis reuniones', 'Mis asistencias'],
        ], $this->labels($menu['nav']));
        $this->assertTrue($menu['nav'][1]['items'][1]['current'], 'Mis evidencias is current on its detail');
        $this->assertSame(['Inicio', 'Evidencias', 'Reuniones', 'Asistencias', 'Perfil'], array_column($menu['mobileNav'], 'label'));
        $this->assertSame('Subida de evidencias', $menu['deadline']['label']);
        $this->assertArrayNotHasKey('route', $menu['nav'][0]['items'][0]);
    }

    public function test_the_new_evidence_entry_and_the_card_leave_when_the_upload_closes(): void
    {
        Configuration::query()->update(['upload_evidences_timestamp' => now()->subDay()]);

        $menu = app(Navigation::class)->for($this->user('STUDENT'), 'home');

        $this->assertNotContains('Nueva evidencia', $this->labels($menu['nav'])['Mis cosas']);
        $this->assertNull($menu['deadline']);
    }

    public function test_a_coordinator_sees_the_pending_count_of_others_and_their_review_bar(): void
    {
        $coordinator = $this->user('STUDENT', 'COORDINATOR');
        Coordinator::forceCreate(['user_id' => $coordinator->id, 'committee_id' => $this->committee->id]);
        $student = $this->user('STUDENT');
        foreach ([$student, $student, $coordinator] as $author) {
            Evidence::create(['title' => 'Montaje', 'description' => 'x', 'hours' => 1, 'status' => 'PENDING', 'user_id' => $author->id, 'committee_id' => $this->committee->id]);
        }

        $menu = app(Navigation::class)->for($coordinator->fresh(), 'coordinator.evidence.list.pending');

        $this->assertSame(['Mi comité', 'Revisar evidencias', 'Mensajes', 'Actividad del comité'], $this->labels($menu['nav'])['Comité']);
        $review = collect($menu['nav'])->firstWhere('label', 'Comité')['items'][1];
        $this->assertSame('Revisar evidencias', $review['label']);
        $this->assertSame(2, $review['count'], 'Their own evidence does not count');
        $this->assertTrue($review['current']);
        $this->assertSame(['Inicio', 'Revisar', 'Evidencias', 'Reuniones', 'Perfil'], array_column($menu['mobileNav'], 'label'));
        $this->assertSame('Validación de evidencias', $menu['deadline']['label']);
    }

    public function test_a_secretary_and_a_register_coordinator_get_their_groups(): void
    {
        $secretary = $this->user('STUDENT', 'SECRETARY');
        Secretary::forceCreate(['user_id' => $secretary->id, 'committee_id' => $this->committee->id]);

        $menu = app(Navigation::class)->for($secretary->fresh(), 'secretary.meeting.manage.minutes.create.step2');
        $this->assertSame(['Reuniones', 'Bonos', 'Listas', 'Mensajes', 'Actividad del comité'], $this->labels($menu['nav'])['Reuniones y bonos']);
        $this->assertTrue(collect($menu['nav'])->firstWhere('label', 'Reuniones y bonos')['items'][0]['current']);
        $this->assertSame('Registro de reuniones', $menu['deadline']['label']);

        $register = app(Navigation::class)->for($this->user('STUDENT', 'REGISTER_COORDINATOR'), 'attendee.create');
        $this->assertSame(['Eventbrite', 'Eventos', 'Asistencias', 'Mensajes'], $this->labels($register['nav'])['Asistencias y eventos']);
        $this->assertSame('Importación de eventos y asistencias', $register['deadline']['label']);
    }

    public function test_management_appears_once_and_the_lecturer_gets_the_lecture_routes(): void
    {
        $menu = app(Navigation::class)->for($this->user('PRESIDENT', 'LECTURE'), 'lecture.user.list');

        $labels = $this->labels($menu['nav']);
        $this->assertSame(1, collect($menu['nav'])->where('label', 'Gestión')->count());
        $this->assertSame(['Configurar curso', 'Alumnos', 'Revisar evidencias', 'Evidencias', 'Reuniones', 'Asistencias', 'Bonos', 'Comités', 'Aleatorizar', 'Integridad', 'Importar', 'Exportar', 'Registro de actividad', 'Mensajes', 'Errores'], $labels['Gestión']);
        $this->assertStringContainsString('/lecture/', collect($menu['nav'])->firstWhere('label', 'Gestión')['items'][0]['href']);
        $this->assertSame(['Inicio', 'Evidencias', 'Alumnos', 'Reuniones', 'Perfil'], array_column($menu['mobileNav'], 'label'));

        $lecturer = app(Navigation::class)->for($this->user('LECTURE'), 'home');
        $this->assertArrayNotHasKey('Mis cosas', $this->labels($lecturer['nav']), 'A lecturer on their own has no evidences');
        $this->assertNull($lecturer['deadline']);

        $sheets = app(Navigation::class)->for($this->user('PRESIDENT'), 'president.meeting.signaturesheet.list');
        $meetings = collect(collect($sheets['nav'])->firstWhere('label', 'Gestión')['items'])->firstWhere('label', 'Reuniones');
        $this->assertTrue($meetings['current'], 'The sections of the meetings keep Reuniones current');

        $president = app(Navigation::class)->for($this->user('PRESIDENT'), 'home');
        $this->assertNotContains('Importar', $this->labels($president['nav'])['Gestión']);
        $this->assertContains('Errores', $this->labels($president['nav'])['Gestión'], 'The president sees the server errors too');
        $this->assertStringContainsString('/president/errors', collect(collect($president['nav'])->firstWhere('label', 'Gestión')['items'])->firstWhere('label', 'Errores')['href']);
    }

    public function test_the_presidency_reviews_its_queue_with_the_pending_count(): void
    {
        $coordinator = $this->user('STUDENT', 'COORDINATOR');
        Coordinator::forceCreate(['user_id' => $coordinator->id, 'committee_id' => $this->committee->id]);
        $student = $this->user('STUDENT');
        foreach ([[$coordinator, $this->committee], [$student, Committee::presidency()], [$student, $this->committee]] as [$author, $committee]) {
            Evidence::create(['title' => 'Montaje', 'description' => 'x', 'hours' => 1, 'status' => 'PENDING', 'user_id' => $author->id, 'committee_id' => $committee->id]);
        }

        $menu = app(Navigation::class)->for($this->user('PRESIDENT'), 'president.review.view');

        $review = collect(collect($menu['nav'])->firstWhere('label', 'Gestión')['items'])->firstWhere('label', 'Revisar evidencias');
        $this->assertSame(route('president.review.all'), $review['href']);
        $this->assertSame(2, $review['count'], 'What the coordinator sends to their committee and what goes to the committee of the presidency');
        $this->assertSame('pendientes', $review['countLabel']);
        $this->assertTrue($review['current']);
        $this->assertSame(['Inicio', 'Revisar', 'Evidencias', 'Alumnos', 'Perfil'], array_column($menu['mobileNav'], 'label'));
        $this->assertSame('Validación de evidencias', $menu['deadline']['label']);

        // the coordinator does not count what they sent, nor does the lecturer get the queue of the presidency
        $coordinatorMenu = app(Navigation::class)->for($coordinator, 'home');
        $this->assertSame(1, collect($coordinatorMenu['nav'])->firstWhere('label', 'Comité')['items'][1]['count']);
        $this->assertNotContains('Revisar evidencias', $this->labels(app(Navigation::class)->for($this->user('LECTURE'), 'home')['nav'])['Gestión']);
    }

    public function test_the_layout_composes_the_navigation_when_the_page_does_not_pass_it(): void
    {
        $this->actingAs($this->user('STUDENT'));

        $html = Blade::render('<x-layout.app title="Mis evidencias"><p>Contenido</p></x-layout.app>');

        $this->assertStringContainsString('Mis evidencias', $html);
        $this->assertStringContainsString('Nueva evidencia', $html);
        $this->assertStringContainsString('aria-label="Navegación principal (móvil)"', $html);
        $this->assertStringContainsString('Subida de evidencias', $html);
    }
}
