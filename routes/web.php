<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MutationController;
use App\Http\Controllers\ReportController;

Route::get("/", function () {
    return view("welcome");
});

Route::get("/dashboard", function () {
    return view("dashboard");
})
    ->middleware(["auth", "verified"])
    ->name("dashboard");

Route::middleware("auth")->group(function () {
    Route::get("/profile", [ProfileController::class, "edit"])->name(
        "profile.edit",
    );
    Route::patch("/profile", [ProfileController::class, "update"])->name(
        "profile.update",
    );
    Route::delete("/profile", [ProfileController::class, "destroy"])->name(
        "profile.destroy",
    );
});

Route::middleware(["auth", "verified"])->group(function () {
    Route::get("/dashboard", [DashboardController::class, "index"])->name(
        "dashboard",
    );

    Route::get("/items", [ItemController::class, "index"])->name("items.index");
    Route::get("/items/{item}", [ItemController::class, "show"])->name(
        "items.show",
    );
    Route::get("/items/{item}/qrcode", [ItemController::class, "qrcode"])->name(
        "items.qrcode",
    );

    Route::get("/mutations", [MutationController::class, "index"])->name(
        "mutations.index",
    );

    Route::middleware(["admin"])->group(function () {
        Route::get("/categories", [CategoryController::class, "index"])->name(
            "categories.index",
        );
        Route::get("/locations", [LocationController::class, "index"])->name(
            "locations.index",
        );
        Route::get("/reports", [ReportController::class, "index"])->name(
            "reports.index",
        );
    });
});

require __DIR__ . "/auth.php";
