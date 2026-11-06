<?php

namespace Tests\Feature\Components;

class AvatarTest extends ComponentTestCase
{
    public function test_it_takes_the_initials_of_the_name_and_the_surname(): void
    {
        $html = $this->render('<x-ui.avatar name="Juan" surname="Pérez" />');

        $this->assertStringContainsString('>JP</span>', $html);
        $this->assertStringContainsString('bg-linear-135 from-aurora-400 to-cyan-400 font-bold text-white', $html);
        $this->assertStringContainsString('grid place-items-center', $html);
        $this->assertCspSafe($html);
    }

    public function test_the_surname_falls_back_to_the_second_word_of_the_name(): void
    {
        $this->assertStringContainsString('>JP</span>', $this->render('<x-ui.avatar name="Juan Pérez" />'));
        $this->assertStringContainsString('>J</span>', $this->render('<x-ui.avatar name="Juan" />'));
        $this->assertStringContainsString('>JP</span>', $this->render('<x-ui.avatar name="  Juan   Pérez " />'));
    }

    public function test_a_compound_name_keeps_the_initial_of_the_surname(): void
    {
        $this->assertStringContainsString('>MG</span>', $this->render('<x-ui.avatar name="María del Mar" surname="García" />'));
    }

    public function test_the_initials_are_multibyte_safe(): void
    {
        $this->assertStringContainsString('>ÁL</span>', $this->render('<x-ui.avatar name="Álvaro" surname="López" />'));
        $this->assertStringContainsString('>ÑÁ</span>', $this->render('<x-ui.avatar name="ñandú" surname="ávila" />'));
    }

    public function test_it_is_decorative_by_default(): void
    {
        $html = $this->render('<x-ui.avatar name="Juan" surname="Pérez" />');

        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringNotContainsString('role=', $html);
        $this->assertStringNotContainsString('aria-label', $html);
    }

    public function test_without_decorative_it_is_an_image_named_after_the_person(): void
    {
        $html = $this->render('<x-ui.avatar name="Juan" surname="Pérez" :decorative="false" />');

        $this->assertStringContainsString('role="img"', $html);
        $this->assertStringContainsString('aria-label="Juan Pérez"', $html);
        $this->assertStringNotContainsString('aria-hidden', $html);
        $this->assertStringContainsString('aria-label="Juan Pérez"', $this->render('<x-ui.avatar name="Juan Pérez" :decorative="false" />'));
        $this->assertStringContainsString('aria-label="&lt;b&gt;Juan&lt;/b&gt;"', $this->render('<x-ui.avatar name="<b>Juan</b>" :decorative="false" />'));
    }

    public function test_a_picture_replaces_the_initials(): void
    {
        $html = $this->render('<x-ui.avatar name="Juan" surname="Pérez" src="/storage/avatars/juan.jpg" />');

        $this->assertStringStartsWith('<img src="/storage/avatars/juan.jpg" alt=""', trim($html));
        $this->assertStringContainsString('size-9 text-xs ring-1 ring-aurora-400/50 ring-offset-2 ring-offset-glass-2 object-cover', $html);
        $this->assertStringContainsString('rounded-full', $html);
        $this->assertStringNotContainsString('JP', $html);
        $this->assertStringNotContainsString('from-aurora-400', $html);
        $this->assertCspSafe($html);

        $named = $this->render('<x-ui.avatar name="Juan" surname="Pérez" src="/storage/avatars/juan.jpg" :decorative="false" />');
        $this->assertStringContainsString('alt="Juan Pérez"', $named);
        $this->assertStringNotContainsString('role=', $named);
    }

    public function test_every_size_maps_to_literal_classes(): void
    {
        $this->assertStringContainsString('size-6 text-[0.625rem] ring-0', $this->render('<x-ui.avatar name="Juan" surname="Pérez" size="sm" />'));
        $this->assertStringContainsString('size-9 text-xs ring-1 ring-aurora-400/50 ring-offset-2 ring-offset-glass-2', $this->render('<x-ui.avatar name="Juan" surname="Pérez" />'));
        $this->assertStringContainsString('size-12 text-sm ring-1 ring-aurora-400/50 ring-offset-2 ring-offset-glass-2', $this->render('<x-ui.avatar name="Juan" surname="Pérez" size="lg" />'));
        $this->assertStringContainsString('shrink-0 rounded-full size-9', $this->render('<x-ui.avatar name="Juan" surname="Pérez" class="ml-2" />'));
        $this->assertStringContainsString('ml-2', $this->render('<x-ui.avatar name="Juan" surname="Pérez" class="ml-2" />'));
    }
}
