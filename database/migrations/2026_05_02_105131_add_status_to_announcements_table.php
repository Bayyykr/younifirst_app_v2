<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->enum('status', ['draft', 'publish'])->default('publish')->after('content');
        });

        // Update view_announcements to include status
        DB::statement('DROP VIEW IF EXISTS view_announcements');
        DB::statement("
            CREATE OR REPLACE VIEW view_announcements AS
            SELECT
                a.announcement_id,
                a.title,
                a.content,
                a.status,
                a.file,
                a.created_at,
                a.deleted_at,
                u.user_id         AS creator_id,
                u.name            AS creator_name,
                u.email           AS creator_email,
                u.role            AS creator_role
            FROM announcements a
            JOIN users u ON a.created_by = u.user_id
            WHERE a.deleted_at IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore view_announcements without status
        DB::statement('DROP VIEW IF EXISTS view_announcements');
        DB::statement("
            CREATE OR REPLACE VIEW view_announcements AS
            SELECT
                a.announcement_id,
                a.title,
                a.content,
                a.file,
                a.created_at,
                a.deleted_at,
                u.user_id         AS creator_id,
                u.name            AS creator_name,
                u.email           AS creator_email,
                u.role            AS creator_role
            FROM announcements a
            JOIN users u ON a.created_by = u.user_id
            WHERE a.deleted_at IS NULL
        ");

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
