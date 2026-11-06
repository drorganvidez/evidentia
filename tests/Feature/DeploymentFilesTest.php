<?php

namespace Tests\Feature;

use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

/**
 * The deployment files run the scheduler beside the app, with the same image and without the migrations or the copy
 * of the assets of its entrypoint, and Watchtower updates both containers.
 */
class DeploymentFilesTest extends TestCase
{
    private function compose(string $file): array
    {
        return Yaml::parseFile(base_path('docker/'.$file));
    }

    public function test_production_runs_the_scheduler_with_the_image_of_the_app(): void
    {
        $services = $this->compose('docker-compose.prod.yml')['services'];
        $scheduler = $services['scheduler'];

        $this->assertSame($services['app']['image'], $scheduler['image']);
        $this->assertSame('evidentia_scheduler_container', $scheduler['container_name']);
        $this->assertSame(['sh', 'scripts/wait-for-db.sh', 'php', 'artisan', 'schedule:work'], $scheduler['entrypoint']);
        $this->assertContains('../.env:/var/www/html/.env', $scheduler['volumes']);
        $this->assertContains('../storage:/var/www/html/storage', $scheduler['volumes']);
        $this->assertNotContains('public_build:/var/www/html/public/build', $scheduler['volumes'], 'The assets belong to app and nginx');
        $this->assertStringContainsString('evidentia_scheduler_container', $services['watchtower']['command']);
        $this->assertStringContainsString('evidentia_app_container', $services['watchtower']['command']);
    }

    public function test_development_runs_the_scheduler_once_vendor_is_installed(): void
    {
        $services = $this->compose('docker-compose.dev.yml')['services'];
        $scheduler = $services['scheduler'];

        $this->assertSame($services['app']['build'], $scheduler['build']);
        $this->assertStringContainsString('vendor/autoload.php', implode(' ', $scheduler['entrypoint']));
        $this->assertStringContainsString('php artisan schedule:work', implode(' ', $scheduler['entrypoint']));
        $this->assertStringNotContainsString('migrate', implode(' ', $scheduler['entrypoint']));
    }

    public function test_the_wait_script_hands_over_to_the_command(): void
    {
        $this->assertStringContainsString('exec "$@"', file_get_contents(base_path('scripts/wait-for-db.sh')));
    }

    public function test_production_caches_the_discovered_listeners_on_start(): void
    {
        $this->assertStringContainsString('php artisan event:cache', file_get_contents(base_path('docker/entrypoint.prod.sh')));
    }
}
