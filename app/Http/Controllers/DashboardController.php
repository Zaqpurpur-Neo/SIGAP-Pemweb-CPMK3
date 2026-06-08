<?php

namespace App\Http\Controllers;

use App\Models\{Item, Mutation, Category};

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            "total_items" => Item::count(),
            "low_stock" => Item::whereColumn(
                "stock",
                "<=",
                "minimum_stock",
            )->count(),
            "mutations_month" => Mutation::whereMonth("date", now()->month)
                ->whereYear("date", now()->year)
                ->count(),
            "total_categories" => Category::count(),
        ];

        $recentMutations = Mutation::with("item")
            ->latest("date")
            ->latest("id")
            ->take(10)
            ->get();

        $lowStockItems = Item::with("category")
            ->whereColumn("stock", "<=", "minimum_stock")
            ->orderBy("stock")
            ->take(10)
            ->get();

        return view(
            "dashboard",
            compact("stats", "recentMutations", "lowStockItems"),
        );
    }
}
