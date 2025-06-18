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
