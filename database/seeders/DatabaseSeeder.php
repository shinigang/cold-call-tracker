<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\Team;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        if (DB::table('users')->count() == 0) {
            DB::transaction(function () {
                return tap(User::create([
                    'name' => config('app.superuser_name'),
                    'email' => config('app.superuser_email'),
                    'password' => Hash::make(config('app.superuser_pass'))
                ]), function (User $user) {
                    $user->ownedTeams()->save(Team::forceCreate([
                        'user_id' => $user->id,
                        'name' => config('app.default_team'),
                        'personal_team' => true,
                    ]));
                });
            });
        }

        $this->call([
            CallStatusSeeder::class
        ]);
    }
}
