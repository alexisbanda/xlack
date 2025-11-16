<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@xlack.com'],
            [
                'name' => 'Admin Xlack',
                'email_verified_at' => now(),
                'password' => bcrypt('admin123'),
            ]
        );

        if (! $admin->currentTeam) {
            $team = $admin->ownedTeams()->create([
                'name' => 'Admin Team',
                'personal_team' => true,
            ]);

            $admin->current_team_id = $team->id;
            $admin->save();
        }
    }
}
