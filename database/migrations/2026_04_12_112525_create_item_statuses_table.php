<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_status', function (Blueprint $table) {
            $table->integer('status_id')->primary();
            $table->string('name_status', 30);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_status');
    }
};
