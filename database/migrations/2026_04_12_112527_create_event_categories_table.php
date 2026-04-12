<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_categories', function (Blueprint $table) {
            $table->integer('category_id')->primary();
            $table->string('name_category', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_categories');
    }
};
