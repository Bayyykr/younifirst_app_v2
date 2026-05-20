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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_email')->default(false)->after('fcm_token');
            $table->boolean('notify_event')->default(false)->after('notify_email');
            $table->boolean('notify_team')->default(false)->after('notify_event');
            $table->boolean('notify_lostfound')->default(false)->after('notify_team');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notify_email', 'notify_event', 'notify_team', 'notify_lostfound']);
        });
    }
};
