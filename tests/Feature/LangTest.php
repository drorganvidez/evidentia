<?php

namespace Tests\Feature;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * The validation, authentication, password and pagination texts come in Spanish, key by key from the framework.
 */
class LangTest extends TestCase
{
    private const FILES = ['validation', 'auth', 'passwords', 'pagination'];

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('es');
    }

    public function test_the_messages_are_in_spanish_with_the_translated_field(): void
    {
        $validator = Validator::make(
            ['surname' => '', 'password' => 'abc', 'email' => 'no-es-un-correo', 'reasonrejection' => 'corto'],
            ['surname' => 'required', 'password' => 'min:6', 'email' => 'email', 'reasonrejection' => 'min:10'],
        );

        $this->assertSame('El campo apellidos es obligatorio.', $validator->errors()->first('surname'));
        $this->assertSame('El campo contraseña debe tener al menos 6 caracteres.', $validator->errors()->first('password'));
        $this->assertSame('El campo correo electrónico debe ser una dirección de correo válida.', $validator->errors()->first('email'));
        $this->assertSame('El campo motivo del rechazo debe tener al menos 10 caracteres.', $validator->errors()->first('reasonrejection'));
    }

    public function test_the_login_and_the_pagination_are_in_spanish(): void
    {
        $this->assertSame('Estas credenciales no coinciden con nuestros registros.', __('auth.failed'));
        $this->assertSame('Demasiados intentos. Vuelve a intentarlo en 30 segundos.', __('auth.throttle', ['seconds' => 30]));
        $this->assertSame('&laquo; Anterior', __('pagination.previous'));
        $this->assertSame('Siguiente &raquo;', __('pagination.next'));
    }

    public function test_every_key_of_the_framework_has_its_spanish_line(): void
    {
        $framework = base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en');

        foreach (self::FILES as $file) {
            $english = array_keys(Arr::dot(Arr::except(require $framework.'/'.$file.'.php', ['custom', 'attributes'])));
            $spanish = array_keys(Arr::dot(Arr::except(require lang_path('es/'.$file.'.php'), ['custom', 'attributes'])));

            $this->assertSame([], array_values(array_diff($english, $spanish)), "Missing in es/{$file}.php");
            $this->assertSame([], array_values(array_diff($spanish, $english)), "Unknown in es/{$file}.php");
        }
    }

    public function test_no_text_uses_a_colon(): void
    {
        foreach (self::FILES as $file) {
            foreach (Arr::dot(require lang_path('es/'.$file.'.php')) as $key => $text) {
                if (! is_string($text)) {
                    continue;
                }
                $this->assertStringNotContainsString(':', preg_replace('/:[a-z_]+/', '', $text), "es/{$file}.php {$key}");
            }
        }
    }
}
