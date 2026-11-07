<?php

namespace App\Support;

use App\Models\Evidence;
use App\View\PlainText;
use App\View\StudentRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Copy of the evidences as they stand: a ZIP with evidencias.csv (one row per current version) and the proofs of each
 * one under pruebas/<uvus>/<id>/. Proofs whose file is gone are listed in the CSV and left out of the ZIP.
 */
final class EvidenceArchive
{
    /**
     * Builds the ZIP in a temporary file and returns its path, together with the number of evidences it holds.
     *
     * @return array{0: string, 1: int}
     */
    public static function build(Builder $evidences): array
    {
        $path = tempnam(sys_get_temp_dir(), 'evidencias');
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No se pudo preparar el archivo.');
        }

        $rows = [['Id', 'Apellidos', 'Nombre', 'UVUS', 'Comité', 'Título', 'Descripción', 'Horas', 'Estado', 'Creada', 'Actualizada', 'Pruebas']];
        $count = 0;
        $evidences->where('last', true)->with(['user', 'committee', 'proofs.file'])->orderBy('id')
            ->each(function (Evidence $evidence) use ($zip, &$rows, &$count) {
                $count++;
                $uvus = Str::slug($evidence->user?->username ?? 'sin-usuario');
                $names = [];
                foreach ($evidence->proofs as $proof) {
                    $file = $proof->file;
                    if ($file === null) {
                        continue;
                    }
                    $name = Str::limit(Str::slug($file->name), 80, '').'-'.$proof->id.'.'.strtolower($file->type);
                    $names[] = $name;
                    if (Storage::exists($file->route)) {
                        $zip->addFromString('pruebas/'.$uvus.'/'.$evidence->id.'/'.$name, Storage::get($file->route));
                    }
                }
                $rows[] = [
                    $evidence->id,
                    $evidence->user?->surname,
                    $evidence->user?->name,
                    $evidence->user?->username,
                    $evidence->committee?->name,
                    $evidence->title,
                    PlainText::fromHtml($evidence->description),
                    $evidence->hours,
                    $evidence->status_label,
                    $evidence->created_at?->format('d/m/Y H:i'),
                    $evidence->updated_at?->format('d/m/Y H:i'),
                    implode(', ', $names),
                ];
            });

        $zip->addFromString('evidencias.csv', StudentRecord::csv($rows));
        $zip->close();

        return [$path, $count];
    }
}
