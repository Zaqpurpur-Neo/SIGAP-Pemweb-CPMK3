<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            "name" => "Administrator",
            "email" => "admin@sigap.test",
            "password" => Hash::make("password"),
            "role" => "admin",
            "email_verified_at" => now(),
        ]);

        User::create([
            "name" => "Staff Satu",
            "email" => "staff1@sigap.test",
            "password" => Hash::make("password"),
            "role" => "staff",
            "email_verified_at" => now(),
        ]);

        User::create([
            "name" => "Staff Dua",
            "email" => "staff2@sigap.test",
            "password" => Hash::make("password"),
            "role" => "staff",
            "email_verified_at" => now(),
        ]);
    }
}
