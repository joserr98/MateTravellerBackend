<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'id' => 1,
                'name' => 'traveler',
                'created_at' => date('d/m/y H:i:s'),
                'updated_at' => date('d/m/y H:i:s')
            ],
            [
                'id' => 2,
                'name' => 'organizer',
                'created_at' => date('d/m/y H:i:s'),
                'updated_at' => date('d/m/y H:i:s')
            ],
            [
                'id' => 3,
                'name' => 'admin',
                'created_at' => date('d/m/y H:i:s'),
                'updated_at' => date('d/m/y H:i:s')
            ]
        ]);
    }
}
