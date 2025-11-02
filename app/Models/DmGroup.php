<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DmGroup extends Model
{
    /**
     * Get the users that belong to this DM group.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'dm_group_user');
    }

    /**
     * Get all messages for this DM group.
     */
    public function messages(): MorphMany
    {
        return $this->morphMany(Message::class, 'messageable');
    }
}
