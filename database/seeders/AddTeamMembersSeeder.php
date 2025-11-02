<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddTeamMembersSeeder extends Seeder
{
    public function run(): void
    {
        $team = Team::find(1); // Christian's Team
        $maria = User::where('email', 'maria@test.com')->first();
        $testUser = User::where('email', 'test@example.com')->first();

        if ($team && $maria) {
            if (!$team->users->contains($maria)) {
                $team->users()->attach($maria->id, ['role' => 'editor']);
                $this->command->info('Maria agregada al equipo de Christian');
            }
        }

        if ($team && $testUser) {
            if (!$team->users->contains($testUser)) {
                $team->users()->attach($testUser->id, ['role' => 'editor']);
                $this->command->info('Test User agregado al equipo de Christian');
            }
        }
    }
}
