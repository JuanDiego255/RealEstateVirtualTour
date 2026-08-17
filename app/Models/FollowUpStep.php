<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Un paso de una secuencia: cuánto esperar, por qué canal y qué mensaje.
 */
class FollowUpStep extends Model
{
    protected $fillable = [
        'sequence_id', 'position', 'delay_hours', 'channel',
        'message_template_id', 'subject', 'body',
    ];

    protected $casts = [
        'position'    => 'integer',
        'delay_hours' => 'integer',
    ];

    public function sequence()
    {
        return $this->belongsTo(FollowUpSequence::class, 'sequence_id');
    }

    public function template()
    {
        return $this->belongsTo(MessageTemplate::class, 'message_template_id');
    }
}
