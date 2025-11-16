<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Message;
use App\Models\Team;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->withPersonalTeam()->create();

        $user = User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Crear un canal de ejemplo dentro del primer equipo disponible
        $team = Team::first();
        if ($team) {
            $channel = Channel::create([
                'team_id' => $team->id,
                'name' => 'general',
                'description' => 'Canal general por defecto',
            ]);

            // Agregar al usuario al canal
            $channel->users()->attach($user->id);

            // Crear un mensaje de ejemplo
            $channel->messages()->create([
                'user_id' => $user->id,
                'body' => '¡Bienvenido al canal #general! 🎉',
            ]);
        }

        // Ensure admin user exists
        $this->call(AdminUserSeeder::class);
    }
}
