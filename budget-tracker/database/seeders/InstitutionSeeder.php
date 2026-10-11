<?php

namespace Database\Seeders;

use App\Models\Institution;
use Illuminate\Database\Seeder;

class InstitutionSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'GCash', 'slug' => 'gcash', 'type' => 'ewallet', 'color' => '#007DFE', 'short_code' => 'GC', 'sort_order' => 1],
            ['name' => 'Maribank', 'slug' => 'maribank', 'type' => 'digital_bank', 'color' => '#2F5BEA', 'short_code' => 'MB', 'sort_order' => 2],
            ['name' => 'UnionDigital Bank', 'slug' => 'uniondigital', 'type' => 'digital_bank', 'color' => '#F47920', 'short_code' => 'UD', 'sort_order' => 3],
            ['name' => 'Maya', 'slug' => 'maya', 'type' => 'ewallet', 'color' => '#0B0B0B', 'text_color' => '#4ED05B', 'short_code' => 'MY', 'sort_order' => 4],
            ['name' => 'BPI', 'slug' => 'bpi', 'type' => 'bank', 'color' => '#B11116', 'short_code' => 'BPI', 'sort_order' => 5],
            ['name' => 'Atome', 'slug' => 'atome', 'type' => 'bnpl', 'color' => '#F5E63A', 'text_color' => '#111111', 'short_code' => 'AT', 'sort_order' => 6],
            ['name' => 'SeaBank', 'slug' => 'seabank', 'type' => 'digital_bank', 'color' => '#FF6D00', 'short_code' => 'SB', 'sort_order' => 20],
            ['name' => 'CIMB', 'slug' => 'cimb', 'type' => 'digital_bank', 'color' => '#EC1C24', 'short_code' => 'CIMB', 'sort_order' => 21],
            ['name' => 'Tonik', 'slug' => 'tonik', 'type' => 'digital_bank', 'color' => '#6D28D9', 'short_code' => 'TK', 'sort_order' => 22],
            ['name' => 'BDO', 'slug' => 'bdo', 'type' => 'bank', 'color' => '#0A3D91', 'short_code' => 'BDO', 'sort_order' => 23],
            ['name' => 'ShopeePay', 'slug' => 'shopeepay', 'type' => 'ewallet', 'color' => '#EE4D2D', 'short_code' => 'SP', 'sort_order' => 24],
            ['name' => 'BillEase', 'slug' => 'billease', 'type' => 'bnpl', 'color' => '#1E90FF', 'short_code' => 'BE', 'sort_order' => 25],
            ['name' => 'Cash', 'slug' => 'cash', 'type' => 'other', 'color' => '#16A34A', 'short_code' => '₱', 'sort_order' => 90],
        ];

        foreach ($rows as $row) {
            Institution::query()->updateOrCreate(['slug' => $row['slug']], $row + [
                'text_color' => '#FFFFFF',
                'logo_path' => 'logos/'.$row['slug'].'.svg',
                'is_system' => true,
            ]);
        }
    }
}
