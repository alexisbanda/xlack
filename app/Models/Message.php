<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'body',
    ];

    /**
     * Mensaje padre (si es reply en hilo)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'parent_message_id');
    }

    /**
     * Respuestas (replies) a este mensaje (hilo)
     */
    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_message_id');
    }

    /**
     * Autor del mensaje.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Recurso al que pertenece el mensaje (Channel, DM, etc.).
     */
    public function messageable(): MorphTo
    {
        return $this->morphTo();
    }
}
