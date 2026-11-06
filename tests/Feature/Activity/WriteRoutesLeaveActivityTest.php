<?php

namespace Tests\Feature\Activity;

use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesAppFixtures;
use Tests\TestCase;

/**
 * Every route that writes is listed in one registry of tests/Feature/Activity/WriteRoutes (as a case, excluded with a
 * reason, covered by another test or pending of a batch), and every case leaves its annotation with the role, the
 * request id and the address of the request, never a password.
 */
final class WriteRoutesLeaveActivityTest extends TestCase
{
    use CreatesAppFixtures, RefreshDatabase;

    /** GET routes with an effect, which count as writes too */
    private const GET_WITH_EFFECT = [
        'registercoordinator.event.load', 'registercoordinator.attendee.load', 'registercoordinator.attendee.export',
        'activity.export', 'upload.remove', 'xls.upload.remove', 'ui.demo.remove', 'notifications.open',
        'coordinator.committee.export', 'profiles.export',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAppFixtures();
    }

    /**
     * The registries of every area, one class per file.
     *
     * @return list<class-string>
     */
    private static function registries(): array
    {
        $files = glob(__DIR__.'/WriteRoutes/*.php') ?: [];
        sort($files);

        return array_map(fn (string $file): string => __NAMESPACE__.'\\WriteRoutes\\'.basename($file, '.php'), $files);
    }

    public static function writes(): array
    {
        $cases = array_merge(...array_map(fn (string $registry): array => $registry::writes(), self::registries()));

        // PHPUnit 12 rejects an empty data provider
        return $cases === [] ? ['sin casos todavía' => [null]] : array_map(fn (array $case): array => [$case], $cases);
    }

    public function test_every_write_route_is_registered(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn (RoutingRoute $route): bool => array_diff($route->methods(), ['GET', 'HEAD']) !== []
                || in_array($route->getName(), self::GET_WITH_EFFECT, true))
            // POST /login has no name
            ->map(fn (RoutingRoute $route): string => $route->getName() ?? implode('|', array_diff($route->methods(), ['HEAD'])).' '.$route->uri())
            ->unique()->sort()->values()->all();

        $listed = collect(self::registries())->flatMap(fn (string $registry): array => [
            ...array_keys($registry::writes()),
            ...array_keys($registry::excluded()),
            ...array_keys($registry::coveredElsewhere()),
            ...array_keys($registry::pending()),
        ])->all();

        $this->assertSame([], array_values(array_diff($routes, $listed)), 'Rutas de escritura sin registrar');
        $this->assertSame([], array_values(array_diff($listed, $routes)), 'Rutas registradas que ya no existen');
        $this->assertSame(count($listed), count(array_unique($listed)), 'Ruta registrada dos veces');
    }

    #[DataProvider('writes')]
    public function test_the_route_leaves_its_annotation(?array $case): void
    {
        if ($case === null) {
            $this->markTestSkipped('Ninguna ruta de escritura instrumentada todavía');
        }

        ($case['setup'] ?? fn () => null)($this, $this->ids);
        $actor = $case['role'] === 'GUEST' ? null : ($this->users[$case['role']] ?? $this->extra[$case['role']]);
        $actor ? $this->actingAs($actor) : auth()->logout();

        $level = ob_get_level();
        ob_start(); // the downloads close the active output buffer
        $response = $this->call($case['method'], ($case['uri'])($this->ids), ($case['data'] ?? fn () => [])($this->ids));
        while (ob_get_level() > $level) {
            ob_end_clean();
        }

        $this->assertLessThan(400, $response->getStatusCode());
        $this->assertNull(session('error'), (string) session('error'));
        foreach ($case['expect'] as $verb) {
            $log = ActivityLog::where('action', $verb)->latest('id')->first();
            $this->assertNotNull($log, "Falta la anotación $verb");
            $this->assertSame($case['acting_role'], $log->acting_role);
            $this->assertNotNull($log->request_id);
            $this->assertSame('127.0.0.1', $log->ip);
            $this->assertStringNotContainsString('password', (string) json_encode($log->details));
        }
        $this->assertSame(1, ActivityLog::whereIn('action', $case['expect'])->distinct()->count('request_id'), 'Las anotaciones de la petición comparten request_id');
    }
}
