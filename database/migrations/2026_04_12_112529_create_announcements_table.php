<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->char('announcement_id', 10)->primary();
            $table->string('title', 100);
            $table->text('content');
            $table->binary('file')->nullable();
            $table->char('created_by', 10);
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_by');
            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
