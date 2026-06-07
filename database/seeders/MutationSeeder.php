<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Item;
use App\Models\User;

class MutationSeeder extends Seeder
{
    public function run(): void
    {
        $items = Item::all();
        $users = User::all();
        $mutations = [];

        foreach ($items as $item) {
            $mutationCount = rand(1, 2);
            for ($i = 0; $i < $mutationCount; $i++) {
                $mutations[] = [
                    "item_id" => $item->id,
                    "type" => rand(0, 1) ? "in" : "out",
                    "quantity" => rand(1, 10),
                    "date" => now()->subDays(rand(1, 30)),
                    "note" => "Mutasi otomatis untuk " . $item->name,
                    "user_id" => $users->random()->id,
                    "created_at" => now(),
                    "updated_at" => now(),
                ];
            }
        }

        DB::table("mutations")->insert($mutations);
    }
}
