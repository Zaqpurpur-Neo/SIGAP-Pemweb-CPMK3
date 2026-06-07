<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("items", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("category_id")
                ->constrained("categories")
                ->onDelete("restrict");
            $table
                ->foreignId("location_id")
                ->constrained("locations")
                ->onDelete("restrict");
            $table->string("name");
            $table->string("code")->unique();
            $table->string("unit");
            $table->integer("stock")->default(0);
            $table->integer("minimum_stock")->default(5);
            $table->string("photo")->nullable();
            $table->text("description")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("items");
    }
};
