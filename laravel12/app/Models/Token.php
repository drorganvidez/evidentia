<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Models\User;

class Token extends Model
{
    protected $table = 'tokens';

    protected $fillable = [
        'token',
        'used',
        'valid_until_timestamp',
        'user_id',
    ];

    /**
     * Determine if the token is valid (not used and not expired).
     */
    public function isValid(): bool
    {
        return !$this->used && Carbon::now()->lt($this->valid_until_timestamp);
    }

    /**
     * The user that owns this token.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
