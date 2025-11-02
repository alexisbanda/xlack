<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChannelMessageController extends Controller
{
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
