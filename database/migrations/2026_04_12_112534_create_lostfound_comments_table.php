<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lostfound_comments', function (Blueprint $table) {
            $table->char('comment_id', 36)->primary();
            $table->char('lostfound_id', 10);
            $table->char('user_id', 10);
            $table->text('comment');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('update_at')->nullable(); // sesuai diagram: update_at (bukan updated_at)

            $table->index(['lostfound_id', 'user_id']);
            $table->foreign('lostfound_id')->references('lostfound_id')->on('lostfound_items')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lostfound_comments');
    }
};
