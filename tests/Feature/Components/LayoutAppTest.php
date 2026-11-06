<?php

namespace Tests\Feature\Components;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LayoutAppTest extends ComponentTestCase
{
    use RefreshDatabase;

    /**
     * @return list<array<string, mixed>>
     */
    private function nav(): array
    {
        return [
            ['label' => null, 'items' => [
                ['label' => 'Inicio', 'href' => '/inicio', 'icon' => 'layout-dashboard', 'current' => true, 'mobile' => true],
                ['label' => 'Mi perfil', 'href' => '/perfil', 'icon' => 'user-round', 'mobile' => true],
            ]],
            ['label' => 'Mis cosas', 'items' => [
                ['label' => 'Mis evidencias', 'href' => '/evidencias', 'icon' => 'file-text', 'count' => 6, 'mobile' => true],
                ['label' => 'Mis reuniones', 'href' => '/reuniones', 'icon' => 'handshake', 'mobile' => true],
                ['label' => 'Mis asistencias', 'href' => '/asistencias', 'icon' => 'ticket', 'mobile' => true],
                ['label' => 'Nueva evidencia', 'href' => '/evidencias/nueva', 'icon' => 'plus', 'mobile' => true],
            ]],
        ];
    }

    private function render_shell(string $extra = '', array $data = []): string
    {
        return $this->render('<x-layout.app title="Inicio" :nav="$nav" :breadcrumbs="$breadcrumbs" '.$extra.'><h1 id="page-title">Hola</h1></x-layout.app>', [
            'nav' => $this->nav(),
            'breadcrumbs' => [['label' => 'Inicio', 'href' => '/inicio'], ['label' => 'Mis evidencias']],
            ...$data,
        ]);
    }

    public function test_the_landmarks_come_in_order_and_main_is_the_target_of_the_skip_link(): void
    {
        $html = $this->render_shell();

        $skip = strpos($html, 'href="#main"');
        $aside = strpos($html, '<aside aria-label="Barra lateral"');
        $header = strpos($html, '<header');
        $notices = strpos($html, 'x-data="notices"');
        $main = strpos($html, '<main id="main" tabindex="-1"');
        $footer = strpos($html, '<footer');
        $bar = strpos($html, 'aria-label="Navegación principal (móvil)"');
        $drawer = strpos($html, '<dialog id="drawer"');

        foreach (compact('skip', 'aside', 'header', 'notices', 'main', 'footer', 'bar', 'drawer') as $name => $position) {
            $this->assertNotFalse($position, $name.' is present');
        }

        $this->assertTrue($skip < $aside && $aside < $header && $header < $notices && $notices < $main && $main < $footer && $footer < $bar && $bar < $drawer, 'DOM order');
        $this->assertMatchesRegularExpression('/<main id="main" tabindex="-1"\s+aria-labelledby="page-title"\s+class="grid content-start gap-6 px-0 pt-2 pb-4 outline-none md:gap-8 md:px-2 md:pb-10">/', $html);
        $this->assertStringContainsString('<title>Inicio · '.config('app.name').'</title>', $html);
        $this->assertMatchesRegularExpression('/<body class="[^"]*pb-safe-nav md:pb-0[^"]*">/', $html);
        $this->assertStringContainsString('<h1 id="page-title">Hola</h1>', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertSame(1, substr_count($html, '<main '));
        $this->assertStringContainsString('md:grid-cols-[5rem_minmax(0,1fr)] md:gap-6 md:p-5 lg:grid-cols-[17rem_minmax(0,1fr)]', $html);
        $this->assertUniqueIds($html);
        $this->assertNoAutofocus($html);
        $this->assertCspSafe($html);
    }

    public function test_the_aurora_can_be_left_out(): void
    {
        $this->assertStringContainsString('animate-drift-1', $this->render_shell());
        $this->assertStringNotContainsString('animate-drift-1', $this->render_shell(':aurora="false"'));
    }

    public function test_main_can_drop_the_reference_to_the_page_title(): void
    {
        $html = $this->render('<x-layout.app :nav="$nav" :labelled-by="false">Hola</x-layout.app>', ['nav' => $this->nav()]);

        $this->assertMatchesRegularExpression('/<main id="main" tabindex="-1"\s+class="/', $html);
        $this->assertStringNotContainsString('aria-labelledby="page-title"', $html);
    }

    public function test_the_navigation_groups_render_in_the_aside_and_in_the_drawer(): void
    {
        $html = $this->render_shell();

        $this->assertSame(2, substr_count($html, '<nav aria-label="Navegación principal"'), 'Aside and drawer');
        $this->assertSame(2, substr_count($html, '<nav aria-label="Mis cosas"'));
        $this->assertSame(2, substr_count($html, '>Mis cosas</p>'), 'Group heading');
        $this->assertStringContainsString('md:max-lg:sr-only', $html, 'The rail hides the heading in the aside');
        $this->assertStringContainsString('hidden md:max-lg:flex', $html, 'Mark-only brand for the rail');
        $this->assertSame(3, substr_count($html, 'aria-current="page"') - substr_count($html, 'aria-current="page" class="truncate'), 'Inicio is current in the aside, the drawer and the bottom bar');

        $plain = $this->render('<x-layout.app :nav="$nav">Hola</x-layout.app>', ['nav' => [['label' => null, 'items' => $this->nav()[0]['items']]]]);

        $this->assertStringNotContainsString('Mis cosas', $plain);
        $this->assertDoesNotMatchRegularExpression('/<nav aria-label="[^"]*"\s+class="mt-2 border-t border-rule pt-4"/', $plain, 'No separator without a labelled group');
    }

    public function test_the_last_breadcrumb_is_the_current_page(): void
    {
        $html = $this->render_shell();

        $this->assertStringContainsString('<li aria-current="page" class="truncate font-semibold text-ink">Mis evidencias</li>', $html);
        $this->assertStringContainsString('href="/inicio"', $html);
    }

    public function test_the_mobile_bar_takes_the_first_five_mobile_items_unless_given(): void
    {
        $html = $this->render_shell();

        preg_match('/<nav aria-label="Navegación principal \(móvil\)".*?<\/nav>/s', $html, $bar);
        $this->assertNotEmpty($bar);
        $this->assertSame(5, substr_count($bar[0], '<li class="min-w-0">'));
        $this->assertStringContainsString('Mis asistencias', $bar[0]);
        $this->assertStringNotContainsString('Nueva evidencia', $bar[0], 'The sixth mobile item is left out');

        $custom = $this->render_shell(':mobile-nav="$mobileNav"', ['mobileNav' => [['label' => 'Inicio', 'href' => '/', 'icon' => 'layout-dashboard']]]);
        preg_match('/<nav aria-label="Navegación principal \(móvil\)".*?<\/nav>/s', $custom, $bar);
        $this->assertSame(1, substr_count($bar[0], '<li class="min-w-0">'));

        $none = $this->render_shell(':mobile-nav="[]"');
        $this->assertStringNotContainsString('Navegación principal (móvil)', $none);
    }

    public function test_the_deadline_the_bell_and_the_search_appear_only_when_given(): void
    {
        $bare = $this->render_shell();

        $this->assertStringNotContainsString('x-data="deadline"', $bare);
        $this->assertStringNotContainsString('role="search"', $bare);
        $this->assertStringNotContainsString('Avisos, ', $bare);

        $full = $this->render_shell(':deadline="$deadline" :notifications="$notifications" :search="$search"', [
            'deadline' => ['label' => 'Subida de evidencias', 'closesAt' => now()->addDays(14), 'opensAt' => now()->subDays(30), 'href' => '/evidencias'],
            'notifications' => ['count' => 3, 'href' => '/avisos'],
            'search' => ['action' => '/evidencias', 'placeholder' => 'Buscar evidencias'],
        ]);

        $this->assertSame(2, substr_count($full, 'x-data="deadline"'), 'Aside and drawer');
        $this->assertStringContainsString('<div class="mt-auto hidden lg:block">', $full, 'Hidden on the rail');
        $this->assertStringContainsString('<span class="sr-only">Avisos, 3 sin leer</span>', $full);
        $this->assertSame(2, substr_count($full, 'role="search"'), 'Topbar and drawer');
        $this->assertStringContainsString('id="field-q-topbar"', $full);
        $this->assertStringContainsString('id="field-q-drawer"', $full);
        $this->assertUniqueIds($full);
        $this->assertCspSafe($full);
    }

    public function test_the_user_menu_and_the_drawer_account_sign_out_with_a_post_form(): void
    {
        $html = $this->render_shell(':user="$user"', [
            'user' => ['name' => 'Juan', 'surname' => 'Pérez', 'roles' => ['Estudiante', 'Coordinador'], 'profileHref' => '/cuenta', 'logoutAction' => '/salir'],
        ]);

        $this->assertStringContainsString('<span class="sr-only">Menú de Juan Pérez</span>', $html);
        $this->assertStringContainsString('>JP</span>', $html);
        $this->assertStringContainsString('Estudiante · Coordinador', $html);
        $this->assertSame(2, substr_count($html, '<form method="post" action="/salir">'), 'User menu and drawer');
        $this->assertSame(2, substr_count($html, 'name="_token"'));
        $this->assertStringContainsString('<ul id="user-menu" role="menu"', $html);
        $this->assertStringContainsString('<h3 id="drawer-account-title"', $html);
        $this->assertStringContainsString('<h3 id="drawer-theme-title"', $html);
        $this->assertSame(2, substr_count($html, 'href="/cuenta"'), 'User menu and drawer');
        $this->assertUniqueIds($html);

        $anonymous = $this->render_shell();

        $this->assertStringNotContainsString('user-menu', $anonymous);
        $this->assertStringNotContainsString('drawer-account-title', $anonymous);
    }

    public function test_the_signed_in_person_fills_the_user_when_none_is_given(): void
    {
        $account = User::factory()->create(['name' => 'Lucía', 'surname' => 'García']);
        $account->roles()->attach(Role::forceCreate(['rol' => 'COORDINATOR', 'slug' => 'coordinator']));
        $this->actingAs($account);

        $html = $this->render_shell();

        $this->assertStringContainsString('<span class="sr-only">Menú de Lucía García</span>', $html);
        $this->assertStringContainsString('Coordinador', $html);
        $this->assertStringContainsString('action="'.route('logout').'"', $html);
        $this->assertStringContainsString('href="'.route('profile.view').'"', $html);
    }

    public function test_the_flash_messages_reach_the_notices_and_the_session_is_read_by_default(): void
    {
        $given = $this->render_shell(':flash="$flash"', ['flash' => [['tone' => 'accepted', 'text' => '<b>Guardada</b>']]]);

        $this->assertStringContainsString('data-messages="[{&quot;tone&quot;:&quot;accepted&quot;,&quot;text&quot;:&quot;&lt;b&gt;Guardada&lt;\/b&gt;&quot;}]"', $given);
        $this->assertStringNotContainsString('<script', preg_replace('/<script nonce="[^"]*">.*?<\/script>/s', '', $given));

        $this->withSession(['error' => 'Fallo']);

        $this->assertStringContainsString('data-messages="'.e(json_encode([['tone' => 'rejected', 'text' => 'Fallo']])).'"', $this->render_shell());
    }

    public function test_the_version_the_links_and_the_subtitle_reach_the_footer_and_the_brand(): void
    {
        $html = $this->render_shell('version="4.0.0" subtitle="Jornadas InnoSoft 2026" :footer-links="$links"', [
            'links' => [['label' => 'Ayuda', 'href' => '/ayuda', 'icon' => 'circle-help']],
        ]);

        $this->assertStringContainsString('>v4.0.0</span>', $html);
        $this->assertStringContainsString('aria-label="Evidentia, Jornadas InnoSoft 2026, inicio"', $html);
        $this->assertStringContainsString('href="/ayuda"', $html);
        $this->assertStringNotContainsString('Repositorio', $html);
    }
}
