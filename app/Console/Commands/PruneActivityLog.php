<?php

namespace App\Console\Commands;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\User;
use Closure;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Retention of the activity log and of the notices. The only writes on activity_log after its insertion: deletes and
 * the emptying of the IP and the user agent, in batches of ids that work the same in SQLite and MySQL.
 */
class PruneActivityLog extends Command
{
    protected $signature = 'activity:prune {--dry-run : Cuenta lo que se borraría sin borrar nada}';

    protected $description = 'Purga el registro de actividad, los datos de red y los avisos antiguos';

    private const BATCH = 1000;

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $now = now();

        $counts = [
            'failed_logins' => $this->delete(
                DB::table('activity_log')->where('action', ActivityAction::LoginFailed->value)
                    ->where('created_at', '<', $now->copy()->subDays((int) config('activity.failed_login_retention_days'))),
                'activity_log', $dry,
            ),
            'entries' => $this->delete(
                DB::table('activity_log')->where('created_at', '<', $now->copy()->subYears((int) config('activity.retention_years'))),
                'activity_log', $dry,
            ),
            'network_cleared' => $this->batches(
                DB::table('activity_log')
                    ->where('created_at', '<', $now->copy()->subDays((int) config('activity.network_data_retention_days')))
                    ->where(fn (Builder $query) => $query->whereNotNull('ip')->orWhereNotNull('user_agent')),
                $dry,
                fn (Collection $ids) => DB::table('activity_log')->whereIn('id', $ids)->update(['ip' => null, 'user_agent' => null]),
            ),
            'read_notifications' => $this->delete(
                DB::table('notifications')->whereNotNull('read_at')
                    ->where('read_at', '<', $now->copy()->subDays((int) config('activity.read_notification_retention_days'))),
                'notifications', $dry,
            ),
            'orphan_notifications' => $this->delete(
                DB::table('notifications')->where('notifiable_type', (new User)->getMorphClass())
                    ->whereNotIn('notifiable_id', DB::table('users')->select('id')),
                'notifications', $dry,
            ),
            'reminders' => $this->delete(
                DB::table('notification_reminders')->where('sent_at', '<', $now->copy()->subYear()),
                'notification_reminders', $dry,
            ),
        ];

        $this->table(['Paso', 'Filas'], [
            ['Accesos fallidos borrados', $counts['failed_logins']],
            ['Anotaciones caducadas borradas', $counts['entries']],
            ['Anotaciones sin IP ni agente', $counts['network_cleared']],
            ['Avisos leídos borrados', $counts['read_notifications']],
            ['Avisos de usuarios borrados', $counts['orphan_notifications']],
            ['Recordatorios borrados', $counts['reminders']],
        ]);

        if ($dry) {
            $this->info('Simulación. No se ha borrado nada.');

            return self::SUCCESS;
        }

        Activity::startRequest();
        Activity::system(ActivityAction::ActivityPruned, details: $counts);

        return self::SUCCESS;
    }

    private function delete(Builder $query, string $table, bool $dry): int
    {
        return $this->batches($query, $dry, fn (Collection $ids) => DB::table($table)->whereIn('id', $ids)->delete());
    }

    /** Applies the change to the matching ids, a thousand at a time; with --dry-run it only counts them. */
    private function batches(Builder $query, bool $dry, Closure $apply): int
    {
        if ($dry) {
            return $query->count();
        }

        $total = 0;
        do {
            $ids = (clone $query)->orderBy('id')->limit(self::BATCH)->pluck('id');
            if ($ids->isNotEmpty()) {
                $apply($ids);
                $total += $ids->count();
            }
        } while ($ids->count() === self::BATCH);

        return $total;
    }
}
