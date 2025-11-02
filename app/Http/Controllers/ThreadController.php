<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class ThreadController extends Controller
{
    /**
     * Obtener un mensaje y sus replies (hilo).
     */
    public function show($id)
    {
        $message = Message::with(['user', 'replies.user'])->findOrFail($id);
        return response()->json($message);
    }

    /**
     * Crear un reply (mensaje hijo) en un hilo.
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'body' => 'required|string|min:1|max:2000',
        ]);

        $parent = Message::findOrFail($id);

        // Crear reply manualmente para setear messageable_id y messageable_type
        $reply = new \App\Models\Message();
        $reply->user_id = $request->user()->id;
        $reply->body = $request->body;
        $reply->parent_message_id = $parent->id;
        $reply->messageable_id = $parent->messageable_id;
        $reply->messageable_type = $parent->messageable_type;
        $reply->save();

        $reply->load('user');

        // Opcional: emitir evento y parsear menciones
        if (class_exists('App\\Events\\NewMessageSent')) {
            \App\Events\NewMessageSent::dispatch($reply);
        }
        if (class_exists('App\\Jobs\\ParseMentions')) {
            \App\Jobs\ParseMentions::dispatch($reply)->onQueue('default');
        }

        return response()->json($reply, 201);
    }
}
