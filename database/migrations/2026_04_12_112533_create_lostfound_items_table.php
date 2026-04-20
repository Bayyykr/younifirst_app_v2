<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lostfound_items', function (Blueprint $table) {
            $table->char('lostfound_id', 10)->primary();
            $table->char('user_id', 10);
            $table->string('item_name', 150);
            $table->text('description');
            $table->string('photo', 255)->nullable();
            $table->string('location', 255);
            $table->integer('status_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->index(['user_id', 'status_id']);
            $table->foreign('user_id')->references('user_id')->on('users');
            $table->foreign('status_id')->references('status_id')->on('item_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lostfound_items');
    }
};
