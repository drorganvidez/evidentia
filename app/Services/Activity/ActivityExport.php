<?php

namespace App\Services\Activity;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The activity log as a CSV for a spreadsheet: UTF-8 with BOM, «;» as separator, dates in ISO 8601 with their offset.
 * A cell that a spreadsheet would read as a formula starts with an apostrophe.
 */
final class ActivityExport
{
    public const HEADERS = [
        'id', 'fecha', 'usuario', 'uvus', 'rol', 'accion', 'accion_texto', 'objeto_tipo', 'objeto_id', 'objeto_titulo',
        'afecta_a', 'comite', 'senales', 'detalles', 'ip', 'agente', 'peticion',
    ];

    public function __construct(private ActivityPresenter $presenter) {}

    public static function filename(): string
    {
        return 'registro-actividad-'.now()->format('Ymd-Hi').'.csv';
    }

    public function stream(Builder $query): StreamedResponse
    {
        return response()->streamDownload(function () use ($query): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, self::HEADERS, ';', '"', '');

            $query->clone()->with(['user', 'targetUser', 'committee'])->lazyById(500)->each(function (ActivityLog $entry) use ($out): void {
                fputcsv($out, array_map(self::cell(...), $this->row($entry)), ';', '"', '');
            });

            fclose($out);
        }, self::filename(), ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return list<string|int|null> */
    public function row(ActivityLog $entry): array
    {
        return [
            $entry->id,
            $entry->created_at?->toIso8601String(),
            $entry->user_id === null ? 'Sistema' : $this->presenter->actorName($entry),
            $entry->user?->username ?? $entry->user_username,
            $entry->acting_role,
            $entry->action,
            $this->presenter->label($entry),
            $entry->subject_type,
            $entry->subject_id,
            $entry->subject_title,
            $entry->targetUser ? trim($entry->targetUser->name.' '.$entry->targetUser->surname) : $entry->target_name,
            $entry->committee?->name ?? $entry->committee_name,
            implode(',', array_map(fn ($flag) => $flag->value, $entry->flagList())),
            $entry->details === null ? null : json_encode($entry->details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $entry->ip,
            $entry->user_agent,
            $entry->request_id,
        ];
    }

    /** Neutralises a value a spreadsheet would run as a formula (=, +, -, @, tab or carriage return first). */
    public static function cell(string|int|null $value): string
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value;
    }
}
