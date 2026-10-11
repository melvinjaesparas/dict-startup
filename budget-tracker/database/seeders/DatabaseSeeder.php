<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Single-user app. Change the password in Settings after first login.
        User::query()->firstOrCreate(
            ['email' => 'justine@astintech.local'],
            ['name' => 'Justine Tabunan', 'password' => 'password'],
        );

        $this->call([
            InstitutionSeeder::class,
            ProfileSeeder::class,
            SampleDataSeeder::class,
        ]);
    }
}
