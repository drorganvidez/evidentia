<?php

namespace App\Notifications;

use App\Enums\ActivityAction;
use App\Models\User;
use ReflectionClass;

/**
 * Every notice of the app, found by file in app/Notifications/Events and app/Notifications/Reminders, so that adding
 * one needs no registration.
 */
final class Catalog
{
    private const FOLDERS = ['Events', 'Reminders'];

    /** @var list<class-string<BaseNotification>>|null */
    private static ?array $classes = null;

    /** @return list<class-string<BaseNotification>> */
    public static function all(): array
    {
        return self::$classes ??= self::discover();
    }

    /** @return list<class-string<BaseNotification>> */
    public static function triggeredBy(string $action): array
    {
        $verb = ActivityAction::tryFrom($action);
        if ($verb === null) {
            return [];
        }

        return array_values(array_filter(self::all(), fn (string $class): bool => in_array($verb, $class::triggers(), true)));
    }

    /**
     * The notices a person can tune, by level.
     *
     * @return array{fixed: list<class-string<BaseNotification>>, important: list<class-string<BaseNotification>>, informative: list<class-string<BaseNotification>>}
     */
    public static function forPreferences(User $user): array
    {
        $groups = ['fixed' => [], 'important' => [], 'informative' => []];

        foreach (self::all() as $class) {
            $audience = $class::audience();
            $reaches = $audience === [] || array_filter($audience, fn (string $role): bool => $user->hasRole($role)) !== [];
            if ($class::inPreferences() && $reaches) {
                $groups[$class::level()->value][] = $class;
            }
        }

        return $groups;
    }

    /** @return class-string<BaseNotification>|null */
    public static function byKey(string $key): ?string
    {
        foreach (self::all() as $class) {
            if ($class::key() === $key) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Replaces the catalogue (tests only).
     *
     * @param  list<class-string<BaseNotification>>  $classes
     */
    public static function using(array $classes): void
    {
        self::$classes = array_values($classes);
    }

    public static function flush(): void
    {
        self::$classes = null;
    }

    /** @return list<class-string<BaseNotification>> */
    private static function discover(): array
    {
        $classes = [];
        foreach (self::FOLDERS as $folder) {
            foreach (glob(app_path('Notifications/'.$folder.'/*.php')) ?: [] as $file) {
                $class = 'App\\Notifications\\'.$folder.'\\'.basename($file, '.php');
                if (class_exists($class) && is_subclass_of($class, BaseNotification::class) && ! (new ReflectionClass($class))->isAbstract()) {
                    $classes[] = $class;
                }
            }
        }
        sort($classes);

        return $classes;
    }
}
