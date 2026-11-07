<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A certificate of attendance to an event (type attendance, one per person and event) or a diploma issued by the
 * presidency or the lecturer (type diploma). The code prints on the PDF and opens the public verification page.
 */
class Certificate extends Model
{
    public const ATTENDANCE = 'attendance';

    public const DIPLOMA = 'diploma';

    protected $fillable = [
        'code', 'type', 'user_id', 'event_id', 'recipient_name', 'title', 'body', 'hours', 'issued_at', 'issued_by',
    ];

    protected function casts(): array
    {
        return ['issued_at' => 'date', 'hours' => 'float'];
    }

    protected static function booted(): void
    {
        static::creating(function (Certificate $certificate) {
            $certificate->code ??= self::newCode();
            $certificate->issued_at ??= now();
        });
    }

    /** Twelve characters without the ones easy to mix up (0/O, 1/I/L), in groups of four */
    public static function newCode(): string
    {
        do {
            $raw = '';
            for ($i = 0; $i < 12; $i++) {
                $raw .= '23456789ABCDEFGHJKMNPQRSTUVWXYZ'[random_int(0, 30)];
            }
            $code = implode('-', str_split($raw, 4));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function isDiploma(): bool
    {
        return $this->type === self::DIPLOMA;
    }

    public function typeLabel(): string
    {
        return $this->isDiploma() ? 'Diploma' : 'Certificado de asistencia';
    }

    public function verifyUrl(): string
    {
        return route('certificate.verify', $this->code);
    }

    public function fileName(): string
    {
        return Str::slug($this->typeLabel().' '.$this->recipient_name).'-'.$this->code.'.pdf';
    }
}
