<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('roles')->insert([
            ['name' => 'Merchance', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Collector', 'created_at' => now(), 'updated_at' => now()],
            // Add more roles as needed
        ]);
    }
}
