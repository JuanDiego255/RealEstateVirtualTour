<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingVisit extends Model
{
    protected $fillable = ['source', 'ip_hash', 'referrer', 'user_agent'];

    public static function record(): void
    {
        $ref = request('ref', '');
        $source = in_array($ref, ['qr', 'link', 'social', 'email']) ? $ref : 'direct';

        static::create([
            'source'     => $source,
            'ip_hash'    => hash('sha256', request()->ip()),
            'referrer'   => substr(request()->header('referer', ''), 0, 500) ?: null,
            'user_agent' => substr(request()->userAgent() ?? '', 0, 300) ?: null,
        ]);
    }
}
