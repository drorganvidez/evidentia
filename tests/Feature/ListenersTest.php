<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Reflector;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

/**
 * The listeners of app/Listeners are only discovered: each pair of event and handle method is registered exactly
 * once, so that no annotation or mail is ever doubled.
 */
class ListenersTest extends TestCase
{
    public function test_every_listener_method_is_registered_exactly_once(): void
    {
        $listeners = Event::getRawListeners();
        $checked = 0;

        foreach (File::files(app_path('Listeners')) as $file) {
            $class = 'App\\Listeners\\'.$file->getFilenameWithoutExtension();
            $reflection = new ReflectionClass($class);

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (! str_starts_with($method->name, 'handle') || ! isset($method->getParameters()[0])) {
                    continue;
                }

                foreach (Reflector::getParameterClassNames($method->getParameters()[0]) as $event) {
                    $registered = array_filter(
                        $listeners[$event] ?? [],
                        fn ($listener): bool => $listener === $class.'@'.$method->name,
                    );
                    $this->assertCount(1, $registered, "{$class}@{$method->name} for {$event}");
                    $checked++;
                }
            }
        }

        $this->assertGreaterThanOrEqual(3, $checked);
    }
}
