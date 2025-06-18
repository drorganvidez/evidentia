<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proof extends Model
{
    protected $table = 'proofs';

    protected $fillable = [
        'evidence_id',
        'file_id',
    ];

    /**
     * Get the evidence associated with this proof.
     */
    public function evidence()
    {
        return $this->belongsTo(Evidence::class);
    }

    /**
     * Get the file attached to this proof.
     */
    public function file()
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Check the integrity of the associated file.
     */
    public function integrity(): bool
    {
        return $this->file->stamp === \Stamp::get_stamp_file($this->file);
    }
}
