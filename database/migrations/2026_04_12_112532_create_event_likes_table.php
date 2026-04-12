<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_likes', function (Blueprint $table) {
            $table->char('like_id', 36)->primary();
            $table->char('event_id', 10);
            $table->char('user_id', 10);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['event_id', 'user_id']);
            $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_likes');
    }
};
