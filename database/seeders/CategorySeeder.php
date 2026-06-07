<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::insert([
            [
                "name" => "Elektronik",
                "description" => "Perangkat elektronik dan komponen",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "Furnitur",
                "description" => "Meja, kursi, dan perabot lainnya",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "ATK",
                "description" => "Alat tulis kantor",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "Peralatan Lab",
                "description" => "Peralatan khusus laboratorium",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "Kebersihan",
                "description" => "Perlengkapan kebersihan",
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);
    }
}
