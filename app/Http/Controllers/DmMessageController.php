<?php

namespace App\Http\Controllers;

use App\Models\DmGroup;
use App\Events\NewMessageSent;
use App\Jobs\ParseMentions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DmMessageController extends Controller
{
    /**
     * Store a new direct message.
     */
    public function store(Request $request, DmGroup $dmGroup)
    {
        $request->validate([
            'body' => 'required|string|max:10000',
        ]);

        // Verificar que el usuario pertenece a este DmGroup
        if (!$dmGroup->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $message = $dmGroup->messages()->create([
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);

        // Cargar relación para el broadcast
        $message->load('user');

        // Emitir evento de nuevo mensaje
        NewMessageSent::dispatch($message);

        // Parsear menciones en background
        ParseMentions::dispatch($message);

        return back();
    }
}
