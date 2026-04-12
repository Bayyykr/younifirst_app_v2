<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->char('member_id', 10)->primary();
            $table->char('team_id', 10);
            $table->char('user_id', 10);
            $table->enum('role', ['leader', 'member'])->default('member');
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->index(['team_id', 'user_id']);
            $table->foreign('team_id')->references('team_id')->on('teams')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
