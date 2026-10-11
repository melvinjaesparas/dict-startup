<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        Profile::query()->updateOrCreate(['slug' => 'personal'], [
            'name' => 'Justine Tabunan', 'type' => 'personal', 'color' => '#2563eb',
        ]);
        Profile::query()->updateOrCreate(['slug' => 'astin-tech'], [
            'name' => 'Astin Tech Solutions', 'type' => 'business', 'color' => '#7c3aed',
        ]);

        Setting::query()->firstOrCreate(['key' => 'usd_php_rate'], ['value' => '58.50']);
        Setting::query()->firstOrCreate(['key' => 'reminder_days'], ['value' => '7']);
    }
}
