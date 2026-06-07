<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        Location::insert([
            [
                "name" => "Lab Komputer A",
                "code" => "LKA",
                "description" => null,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "Lab Komputer B",
                "code" => "LKB",
                "description" => null,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "Ruang Dosen",
                "code" => "RDN",
                "description" => null,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "Gudang",
                "code" => "GDG",
                "description" => null,
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);
    }
}
