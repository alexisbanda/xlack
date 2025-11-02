<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class FixTeamMembersSeeder extends Seeder
{
    public function run(): void
    {
        $team1 = Team::find(1);
        $christian = User::find(1);
        $maria = User::find(3);
        $testUser = User::find(2);

        // Agregar a Christian si no está
        if ($team1 && $christian && !$team1->users->contains($christian)) {
            $team1->users()->attach($christian->id, ['role' => 'owner']);
            $this->command->info('Christian agregado al Team 1');
        }

        // Actualizar current_team_id de Christian
        if ($christian) {
            $christian->current_team_id = 1;
            $christian->save();
            $this->command->info('Christian current_team_id actualizado a 1');
        }

        // Verificar todos están en el team
        if ($team1) {
            $this->command->info('Miembros del Team 1:');
            foreach ($team1->users as $user) {
                $this->command->info('  - ' . $user->name);
            }
        }
    }
}
