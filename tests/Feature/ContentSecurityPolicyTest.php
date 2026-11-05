<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * The CSP middleware runs for the whole "web" group. The header's nonce must be the one @vite prints,
 * the dev server is only allowed while Vite is running hot, and the report endpoint accepts both formats.
 */
class ContentSecurityPolicyTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        parent::setUp();

        // The base TestCase swaps Vite for a stub; here the real one proves the nonce reaches the HTML.
        $this->withVite();

        $this->tmp = sys_get_temp_dir().'/evidentia-csp-'.uniqid();
        File::ensureDirectoryExists($this->tmp.'/build');

        // A minimal page that goes through the "web" group and uses @vite.
        Route::middleware('web')->get('/_csp/page', fn () => Blade::render("@vite(['resources/js/app.js'])"));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->tmp);

        parent::tearDown();
    }

    public function test_report_only_is_the_default_while_the_old_layout_remains(): void
    {
        $this->useBuiltManifest();

        $response = $this->get('/_csp/page')->assertOk();

        $response->assertHeaderMissing('Content-Security-Policy');
        $this->assertStringContainsString("script-src 'self' 'nonce-", (string) $response->headers->get('Content-Security-Policy-Report-Only'));
    }

    public function test_reports_are_not_requested_unless_configured(): void
    {
        $this->useBuiltManifest();
        config(['csp.mode' => 'enforce']);

        $response = $this->get('/_csp/page')->assertOk();

        $this->assertStringNotContainsString('report-', $this->enforcedPolicy($response));
        $response->assertHeaderMissing('Reporting-Endpoints');
    }

    public function test_header_nonce_matches_every_nonce_emitted_by_vite_in_build_mode(): void
    {
        $this->useBuiltManifest();
        config(['csp.mode' => 'enforce']);

        $response = $this->get('/_csp/page')->assertOk();

        $csp = $this->enforcedPolicy($response);
        $nonce = $this->nonceFromPolicy($csp);

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{40}$/', $nonce);

        preg_match_all('/nonce="([^"]+)"/', $response->getContent(), $matches);

        $this->assertGreaterThanOrEqual(2, count($matches[1]), 'Expected a nonce on the stylesheet link and on the module script');
        $this->assertSame([$nonce], array_values(array_unique($matches[1])));

        $response->assertSee('<script type="module"', false);
        $response->assertSee('rel="stylesheet"', false);

        $this->assertStringNotContainsString("'unsafe-inline'", $csp);
        $this->assertStringNotContainsString("'unsafe-eval'", $csp);
        $this->assertStringNotContainsString('localhost:5173', $csp);
    }

    public function test_policy_contains_the_agreed_directives(): void
    {
        $this->useBuiltManifest();
        config(['csp.mode' => 'enforce', 'csp.report' => true]);

        $response = $this->get('/_csp/page')->assertOk();
        $csp = $this->enforcedPolicy($response);
        $nonce = $this->nonceFromPolicy($csp);
        $reportUrl = route('csp.report');

        foreach ([
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'",
            "style-src 'self' 'nonce-{$nonce}'",
            "img-src 'self' data:",
            "font-src 'self'",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
            "report-uri {$reportUrl}",
            'report-to csp',
        ] as $directive) {
            $this->assertStringContainsString($directive, $csp);
        }

        $response->assertHeader('Reporting-Endpoints', 'csp="'.$reportUrl.'"');
    }

    public function test_dev_server_origins_are_allowed_only_while_vite_is_running_hot(): void
    {
        config(['csp.mode' => 'enforce']);
        File::put($this->tmp.'/hot', 'http://localhost:5173');
        Vite::useHotFile($this->tmp.'/hot');

        $response = $this->get('/_csp/page')->assertOk();
        $csp = $this->enforcedPolicy($response);
        $nonce = $this->nonceFromPolicy($csp);

        $this->assertStringContainsString("script-src 'self' 'nonce-{$nonce}' http://localhost:5173", $csp);
        $this->assertStringContainsString("style-src 'self' 'nonce-{$nonce}' http://localhost:5173", $csp);
        $this->assertStringContainsString("connect-src 'self' http://localhost:5173 ws://localhost:5173", $csp);

        // The HMR client carries the nonce too.
        $this->assertMatchesRegularExpression(
            '#<script type="module" src="http://localhost:5173/@vite/client" nonce="'.preg_quote($nonce, '#').'"#',
            $response->getContent()
        );
    }

    public function test_nonce_is_fresh_on_every_request(): void
    {
        $this->useBuiltManifest();
        config(['csp.mode' => 'enforce']);

        $first = $this->nonceFromPolicy($this->enforcedPolicy($this->get('/_csp/page')));
        $second = $this->nonceFromPolicy($this->enforcedPolicy($this->get('/_csp/page')));

        $this->assertNotSame($first, $second);
    }

    public function test_upgrade_insecure_requests_is_only_added_when_configured(): void
    {
        $this->useBuiltManifest();
        config(['csp.mode' => 'enforce']);

        $this->assertStringNotContainsString('upgrade-insecure-requests', $this->enforcedPolicy($this->get('/_csp/page')));

        config(['csp.upgrade_insecure_requests' => true]);

        $this->assertStringContainsString('upgrade-insecure-requests', $this->enforcedPolicy($this->get('/_csp/page')));
    }

    public function test_route_level_enforce_wins_over_global_report_only(): void
    {
        $this->useBuiltManifest();

        Route::middleware(['web', 'csp:enforce'])->get('/_csp/new-layout', fn () => Blade::render("@vite(['resources/js/app.js'])"));

        $response = $this->get('/_csp/new-layout')->assertOk();

        $response->assertHeaderMissing('Content-Security-Policy-Report-Only');

        $csp = $this->enforcedPolicy($response);
        $nonce = $this->nonceFromPolicy($csp);

        // The nonce in the header is the one that was printed in the HTML.
        $response->assertSee('nonce="'.$nonce.'"', false);
    }

    public function test_file_downloads_keep_working_and_carry_the_header(): void
    {
        config(['csp.mode' => 'enforce']);
        Storage::fake('local');
        Storage::put('acta.pdf', '%PDF-1.4 fake');

        Route::middleware('web')->get('/_csp/download', fn () => Storage::download('acta.pdf'));

        $response = $this->get('/_csp/download')->assertOk();

        $this->assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString("default-src 'self'", $this->enforcedPolicy($response));
    }

    public function test_header_is_not_sent_when_disabled(): void
    {
        $this->useBuiltManifest();
        config(['csp.enabled' => false]);

        $this->get('/_csp/page')->assertOk()
            ->assertHeaderMissing('Content-Security-Policy')
            ->assertHeaderMissing('Content-Security-Policy-Report-Only');
    }

    public function test_report_endpoint_logs_reporting_api_payloads(): void
    {
        $log = $this->useFileLogChannel();

        $payload = json_encode([[
            'type' => 'csp-violation',
            'age' => 10,
            'url' => 'https://evidentia.test/home',
            'user_agent' => 'PHPUnit',
            'body' => [
                'documentURL' => 'https://evidentia.test/home',
                'effectiveDirective' => 'script-src-elem',
                'blockedURL' => 'inline',
                'sourceFile' => 'https://evidentia.test/home',
                'lineNumber' => 42,
                'sample' => 'alert(1)',
                'disposition' => 'enforce',
            ],
        ]]);

        $this->call('POST', '/csp-report', [], [], [], ['CONTENT_TYPE' => 'application/reports+json'], $payload)
            ->assertNoContent();

        $written = File::get($log);

        $this->assertStringContainsString('CSP violation', $written);
        $this->assertStringContainsString('"directive":"script-src-elem"', $written);
        $this->assertStringContainsString('"blocked":"inline"', $written);
        $this->assertStringContainsString('"format":"report-to"', $written);
    }

    public function test_report_endpoint_logs_legacy_report_uri_payloads(): void
    {
        $log = $this->useFileLogChannel();

        $payload = json_encode(['csp-report' => [
            'document-uri' => 'https://evidentia.test/home',
            'violated-directive' => 'style-src-attr',
            'effective-directive' => 'style-src-attr',
            'blocked-uri' => 'inline',
            'line-number' => 7,
        ]]);

        $this->call('POST', '/csp-report', [], [], [], ['CONTENT_TYPE' => 'application/csp-report'], $payload)
            ->assertNoContent();

        $written = File::get($log);

        $this->assertStringContainsString('"directive":"style-src-attr"', $written);
        $this->assertStringContainsString('"format":"report-uri"', $written);
    }

    public function test_report_endpoint_rejects_garbage(): void
    {
        $this->call('POST', '/csp-report', [], [], [], ['CONTENT_TYPE' => 'application/reports+json'], 'not json')
            ->assertStatus(400);

        $this->call('POST', '/csp-report', [], [], [], ['CONTENT_TYPE' => 'application/reports+json'], str_repeat('a', 70000))
            ->assertStatus(413);
    }

    /**
     * Pretends `npm run build` ran: a Vite manifest inside a temporary public/ directory.
     */
    private function useBuiltManifest(): void
    {
        $this->app->usePublicPath($this->tmp);

        File::put($this->tmp.'/build/manifest.json', json_encode([
            'resources/js/app.js' => [
                'file' => 'assets/app-Bx1.js',
                'src' => 'resources/js/app.js',
                'isEntry' => true,
                'css' => ['assets/app-Cy2.css'],
            ],
            'resources/css/app.css' => [
                'file' => 'assets/app-Cy2.css',
                'src' => 'resources/css/app.css',
                'isEntry' => true,
            ],
        ]));
    }

    private function useFileLogChannel(): string
    {
        $path = $this->tmp.'/csp.log';

        config([
            'logging.channels.csp_test' => ['driver' => 'single', 'path' => $path, 'level' => 'debug'],
            'csp.log_channel' => 'csp_test',
        ]);

        return $path;
    }

    private function enforcedPolicy(TestResponse $response): string
    {
        $response->assertHeader('Content-Security-Policy');

        return (string) $response->headers->get('Content-Security-Policy');
    }

    private function nonceFromPolicy(string $csp): string
    {
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-([^']+)'/", $csp);
        preg_match("/script-src 'self' 'nonce-([^']+)'/", $csp, $m);

        return $m[1];
    }
}
