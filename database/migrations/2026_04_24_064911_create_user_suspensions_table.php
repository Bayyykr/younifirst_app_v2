<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_suspensions', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('duration'); // e.g., '1', '7', '30', 'custom'
            $table->text('reason');
            $table->text('internal_notes')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_suspensions');
    }
};
