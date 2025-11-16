<?php

namespace App\Http\Controllers;

use App\Models\DmGroup;
use App\Events\NewMessageSent;
use App\Jobs\ParseMentions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class DmMessageController extends Controller
{
    /**
     * List recent direct messages for a DM group.
     */
    public function index(Request $request, DmGroup $dmGroup): JsonResponse
    {
        // Verify that the authenticated user belongs to this DmGroup
        if (! $dmGroup->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $messages = $dmGroup->messages()
            ->with('user:id,name')
            ->latest('id')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json(['data' => $messages]);
    }

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
