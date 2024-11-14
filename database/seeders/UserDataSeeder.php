<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert a dummy user into the user_data table
        DB::table('user_data')->insert([
            'email_id' => 'example@example.com',
            'password' => bcrypt('password123'), // Encrypt the password
        ]);
    }
}
