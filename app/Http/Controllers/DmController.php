<?php

namespace App\Http\Controllers;

use App\Models\DmGroup;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class DmController extends Controller
{
    /**
     * Show direct message with a user.
     */
    public function show(User $user)
    {
        $authUser = Auth::user();

        // No puede mandarse mensajes a sí mismo
        if ($authUser->id === $user->id) {
            return Redirect::route('dashboard');
        }

        // Buscar o crear DmGroup entre estos dos usuarios
        $dmGroup = DmGroup::whereHas('users', function ($query) use ($authUser) {
            $query->where('user_id', $authUser->id);
        })->whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->first();

        if (!$dmGroup) {
            $dmGroup = DmGroup::create();
            $dmGroup->users()->attach([$authUser->id, $user->id]);
        }

        return Redirect::route('dashboard', ['dm' => $dmGroup->id]);
    }
}
