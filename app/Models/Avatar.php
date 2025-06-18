<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avatar extends Model
{
    protected $table = 'avatars'; // Laravel espera 'avatars' por defecto, así que esta línea es necesaria si usas el singular

    protected $fillable = [
        'user_id',
        'file_id',
    ];

    /**
     * Get the user that owns the avatar.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the file associated with the avatar.
     */
    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
