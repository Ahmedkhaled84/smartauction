<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['name' => 'admin'],
            [
                'email' => 'admin@smartauction.local',
                'password' => Hash::make('123'),
            ]
        );

        User::updateOrCreate(
            ['name' => 'ak'],
            [
                'email' => 'ak@smartauction.local',
                'password' => Hash::make('321'),
            ]
        );
    }
}
