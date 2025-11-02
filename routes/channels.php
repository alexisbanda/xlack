<?php

use Illuminate\Support\Facades\Broadcast;

// Canal de notificaciones por usuario (por defecto de Laravel)
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privado por cada Channel (sala de chat)
Broadcast::channel('channel.{channelId}', function ($user, int $channelId) {
    return $user->channels()->whereKey($channelId)->exists();
});

// Canal privado por cada DmGroup (mensajes directos)
Broadcast::channel('dm.{dmGroupId}', function ($user, int $dmGroupId) {
    return $user->dmGroups()->whereKey($dmGroupId)->exists();
});
