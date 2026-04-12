<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->char('team_id', 10)->primary();
            $table->string('team_name', 50);
            $table->string('competition_name', 100);
            $table->text('description');
            $table->integer('max_member');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('update_at')->nullable();
            $table->timestamp('delete_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
