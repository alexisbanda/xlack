<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\User;
use App\Notifications\UserMentioned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ParseMentions implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Message $message) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $body = (string) $this->message->body;

        // Regex simple para @menciones por nombre (palabras alfanuméricas)
        preg_match_all('/@(\w+)/u', $body, $matches);
        $usernames = collect($matches[1] ?? [])->unique()->values();

        if ($usernames->isEmpty()) {
            return;
        }

        // Buscar usuarios por nombre exacto (case-insensitive)
        $users = User::query()
            ->whereIn('name', $usernames)
            ->get();

        foreach ($users as $user) {
            $user->notify(new UserMentioned($this->message));
        }
    }
}
