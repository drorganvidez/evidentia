<?php

namespace Tests\Feature\Activity;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The partial that prints a history: one event per annotation, with who, the summary and the signals.
 */
class ActivityLogPartialTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prints_each_annotation_with_who_the_summary_and_the_signals(): void
    {
        $coordinator = User::factory()->create(['name' => 'Carlos', 'surname' => 'Navarro']);
        $entries = collect([
            ActivityLog::create([
                'created_at' => now(), 'action' => 'evidence.rejected', 'user_id' => $coordinator->id, 'acting_role' => 'COORDINATOR',
                'committee_name' => 'Logística', 'details' => ['reason' => 'Falta el certificado.'],
            ]),
            ActivityLog::create([
                'created_at' => now(), 'action' => 'evidence.published', 'user_id' => $coordinator->id,
                'details' => ['version' => 2, 'hours' => 2], 'flags' => ['role_overlap'], 'flagged' => true,
            ]),
            ActivityLog::create(['created_at' => now(), 'action' => 'notification.failed']),
        ]);

        $html = view('activity.partials.log', ['entries' => $entries, 'viewer' => $coordinator, 'label' => 'Historial de la evidencia'])->render();

        $this->assertStringContainsString('aria-label="Historial de la evidencia"', $html);
        $this->assertStringContainsString('Evidencia rechazada', $html);
        $this->assertStringContainsString('por Carlos Navarro como coordinador de Logística', preg_replace('/\s+/', ' ', $html));
        $this->assertStringContainsString('«Falta el certificado.»', html_entity_decode($html));
        $this->assertStringContainsString('Versión 2', $html);
        $this->assertSame(1, substr_count($html, 'Versión 2'));
        $this->assertStringContainsString('2,00 h', $html);
        $this->assertStringContainsString('Doble papel', $html);
        $this->assertStringContainsString('Sistema', $html);
        $this->assertStringContainsString('Correo no enviado', $html);
        $this->assertStringNotContainsString('Sin anotaciones', $html);
    }

    public function test_an_empty_history_says_so(): void
    {
        $html = view('activity.partials.log', ['entries' => collect(), 'viewer' => User::factory()->create()])->render();

        $this->assertStringContainsString('Sin anotaciones', $html);
        $this->assertStringNotContainsString('<ol', $html);
    }

    public function test_the_partial_contains_nothing_the_policy_would_block(): void
    {
        $source = File::get(resource_path('views/activity/partials/log.blade.php'));

        $this->assertDoesNotMatchRegularExpression('/<script|\sstyle=|\son[a-z]+=/i', $source);
    }
}
