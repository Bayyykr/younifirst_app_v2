<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (! Schema::hasColumn('announcements', 'publish_at')) {
                $table->timestamp('publish_at')->nullable()->after('created_at');
            }

            if (! Schema::hasColumn('announcements', 'notified_at')) {
                $table->timestamp('notified_at')->nullable()->after('publish_at');
            }
        });

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
                a.publish_at,
                a.notified_at,
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

    public function down(): void
    {
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

        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'notified_at')) {
                $table->dropColumn('notified_at');
            }

            if (Schema::hasColumn('announcements', 'publish_at')) {
                $table->dropColumn('publish_at');
            }
        });
    }
};
