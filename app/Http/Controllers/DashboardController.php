<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\DmGroup;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // Asegurar que el equipo actual tenga un canal "general" y que el usuario esté unido a él
        $team = method_exists($user, 'currentTeam') ? $user->currentTeam : null;
        if (! $team) {
            $team = $user->teams()->first();
        }
        if ($team) {
            $general = Channel::firstOrCreate(
                ['team_id' => $team->id, 'name' => 'general'],
                ['description' => 'Canal general por defecto']
            );

            // Unir al usuario si aún no pertenece
            if (! $user->channels()->whereKey($general->id)->exists()) {
                $user->channels()->attach($general->id);

                // Mensaje de bienvenida si el canal fue recién creado
                if ($general->wasRecentlyCreated) {
                    $general->messages()->create([
                        'user_id' => $user->id,
                        'body' => '¡Bienvenido al canal #general! 🎉',
                    ]);
                }
            }
        }

        // Cargar canales del usuario (solo id y nombre)
        $channels = $user->channels()->select('channels.id', 'channels.name')->orderBy('name')->get();

        // Cargar DMs del usuario (con el nombre del otro usuario)
        $dms = $user->dmGroups()->with(['users' => function ($q) use ($user) {
            $q->where('users.id', '!=', $user->id)->select('users.id', 'users.name');
        }])->get()->map(function ($dmGroup) {
            $otherUser = $dmGroup->users->first();
            return [
                'id' => $dmGroup->id,
                'name' => $otherUser ? $otherUser->name : 'Unknown',
                'user_id' => $otherUser ? $otherUser->id : null,
            ];
        });

        // Determinar el contexto activo: canal o DM
        $activeChannel = null;
        $activeDm = null;

        if ($request->has('dm')) {
            // Modo DM
            $dmId = (int) $request->query('dm');
            $activeDm = DmGroup::query()
                ->whereKey($dmId)
                ->whereHas('users', fn ($q) => $q->whereKey($user->id))
                ->with([
                    'messages' => function ($q) {
                        $q->latest('id')->limit(50)->with('user:id,name');
                    },
                    'users' => function ($q) use ($user) {
                        $q->where('users.id', '!=', $user->id)->select('users.id', 'users.name');
                    }
                ])
                ->first();

            if ($activeDm) {
                $otherUser = $activeDm->users->first();
                $activeDm = [
                    'id' => $activeDm->id,
                    'name' => $otherUser ? $otherUser->name : 'Unknown',
                    'messages' => $activeDm->messages->reverse()->values(),
                    'type' => 'dm',
                ];
            }
        } elseif ($channels->isNotEmpty()) {
            // Modo canal (por defecto)
            $activeId = (int) $request->query('channel', $channels->first()->id);
            $activeChannel = Channel::query()
                ->whereKey($activeId)
                ->whereHas('users', fn ($q) => $q->whereKey($user->id))
                ->with(['messages' => function ($q) {
                    $q->latest('id')->limit(50)->with('user:id,name');
                }])
                ->first();

            if ($activeChannel) {
                $activeChannel = [
                    'id' => $activeChannel->id,
                    'name' => $activeChannel->name,
                    'messages' => $activeChannel->messages->reverse()->values(),
                    'type' => 'channel',
                ];
            }
        }

        // Obtener otros usuarios del equipo para poder iniciar DMs
        $teamMembers = collect();
        if ($team) {
            $teamMembers = $team->users()->where('users.id', '!=', $user->id)->select('users.id', 'users.name')->get();
        }

        return Inertia::render('Dashboard', [
            'channels' => $channels,
            'dms' => $dms,
            'teamMembers' => $teamMembers,
            'activeChannel' => $activeChannel,
            'activeDm' => $activeDm,
        ]);
    }
}
