<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus truncate, migrate:fresh sudah bersihkan semua tabel

        User::create([
            "name" => "Administrator",
            "email" => "admin@sigap.test",
            "password" => Hash::make("password"),
            "role" => "admin",
        ]);

        User::create([
            "name" => "Staff Satu",
            "email" => "staff1@sigap.test",
            "password" => Hash::make("password"),
            "role" => "staff",
        ]);

        User::create([
            "name" => "Staff Dua",
            "email" => "staff2@sigap.test",
            "password" => Hash::make("password"),
            "role" => "staff",
        ]);
    }
}
