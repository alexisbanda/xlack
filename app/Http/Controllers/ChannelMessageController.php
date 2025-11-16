<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChannelMessageController extends Controller
{
    /**
     * List recent messages for a channel.
     */
    public function index(Request $request, Channel $channel): JsonResponse
    {
        // Ensure the authenticated user belongs to the channel
        if (! $request->user()->channels()->whereKey($channel->id)->exists()) {
            abort(403);
        }

        $messages = $channel->messages()
            ->with('user:id,name')
            ->latest('id')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json(['data' => $messages]);
    }

    /**
     * Store a newly created message in storage.
     */
    public function store(Request $request, Channel $channel): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        // Asegurar que el usuario pertenece al canal
        if (! $request->user()->channels()->whereKey($channel->id)->exists()) {
            abort(403);
        }

        $message = $channel->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        // Evento en tiempo real
        \App\Events\NewMessageSent::dispatch($message);

        // Procesamiento asíncrono de menciones
        \App\Jobs\ParseMentions::dispatch($message)->onQueue('default');

        return back();
    }
}
