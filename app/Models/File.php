<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $table = 'files';

    protected $fillable = [
        'name',
        'type',
        'route',
        'size',
        'stamp',
    ];

    /** Types the browser shows by itself, by extension; SVG stays out, since it can carry scripts. */
    public const PREVIEWABLE = [
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
    ];

    /** Mime type the browser previews the file with, or null when it can only be downloaded. */
    public function previewMime(): ?string
    {
        return self::PREVIEWABLE[strtolower((string) $this->type)] ?? null;
    }

    /**
     * Get the size of the file in human-readable format.
     */
    public function sizeForHuman(): string
    {
        $bytes = $this->size;

        $GB = 1_000_000_000;
        $MB = 1_000_000;
        $KB = 1_000;

        if ($bytes >= $GB) {
            return round($bytes / $GB, 2).' GB';
        }

        if ($bytes >= $MB) {
            return round($bytes / $MB, 2).' MB';
        }

        if ($bytes >= $KB) {
            return round($bytes / $KB, 2).' KB';
        }

        return $bytes.' B';
    }
}
