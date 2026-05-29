<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
        });

        Schema::table('teams', function (Blueprint $table) {
            if (! Schema::hasColumn('teams', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
        });

        Schema::table('team_members', function (Blueprint $table) {
            if (! Schema::hasColumn('team_members', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
        });

        DB::statement("\n            CREATE OR REPLACE VIEW view_events AS\n            SELECT\n                e.event_id,\n                e.title,\n                e.description,\n                e.start_date,\n                e.end_date,\n                e.location,\n                e.poster,\n                e.status,\n                e.rejection_reason,\n                e.created_at,\n                e.updated_at,\n                e.deleted_at,\n                ec.category_id,\n                ec.name_category,\n                u.user_id         AS creator_id,\n                u.name            AS creator_name,\n                u.email           AS creator_email,\n                (\n                    SELECT COUNT(*)\n                    FROM event_likes el\n                    WHERE el.event_id = e.event_id\n                )                 AS total_likes\n            FROM events e\n            JOIN event_categories ec ON e.category_id = ec.category_id\n            JOIN users u             ON e.created_by  = u.user_id\n        ");

        DB::statement("\n            CREATE OR REPLACE VIEW view_teams AS\n            SELECT\n                t.team_id,\n                t.team_name,\n                t.competition_name,\n                t.description,\n                t.max_member,\n                t.status,\n                t.rejection_reason,\n                t.competition_level,\n                t.achievement_rank,\n                t.photo_activity,\n                t.photo_certificate,\n                t.created_at,\n                t.updated_at,\n                t.deleted_at,\n                (\n                    SELECT COUNT(*)\n                    FROM team_members tm\n                    WHERE tm.team_id = t.team_id\n                      AND tm.status  = 'active'\n                )                   AS current_member_count,\n                (\n                    SELECT COUNT(*)\n                    FROM team_members tm\n                    WHERE tm.team_id = t.team_id\n                      AND tm.status  = 'pending'\n                )                   AS pending_member_count,\n                (\n                    SELECT u.name\n                    FROM team_members tm\n                    JOIN users u ON tm.user_id = u.user_id\n                    WHERE tm.team_id = t.team_id\n                      AND tm.role    = 'leader'\n                    LIMIT 1\n                )                   AS leader_name,\n                (\n                    SELECT u.user_id\n                    FROM team_members tm\n                    JOIN users u ON tm.user_id = u.user_id\n                    WHERE tm.team_id = t.team_id\n                      AND tm.role    = 'leader'\n                    LIMIT 1\n                )                   AS leader_id\n            FROM teams t\n            WHERE t.deleted_at IS NULL\n        ");

        DB::statement("\n            CREATE OR REPLACE VIEW view_team_members AS\n            SELECT\n                tm.member_id,\n                tm.role          AS member_role,\n                tm.status        AS member_status,\n                tm.rejection_reason,\n                tm.portfolio,\n                tm.proposed_role,\n                tm.description,\n                t.team_id,\n                t.team_name,\n                t.competition_name,\n                t.max_member,\n                u.user_id,\n                u.name           AS user_name,\n                u.email          AS user_email,\n                u.nim,\n                u.prodi,\n                u.status         AS user_status\n            FROM team_members tm\n            JOIN teams t ON tm.team_id = t.team_id\n            JOIN users  u ON tm.user_id  = u.user_id\n        ");
    }

    public function down(): void
    {
        DB::statement("\n            CREATE OR REPLACE VIEW view_team_members AS\n            SELECT\n                tm.member_id,\n                tm.role          AS member_role,\n                tm.status        AS member_status,\n                tm.portfolio,\n                tm.proposed_role,\n                tm.description,\n                t.team_id,\n                t.team_name,\n                t.competition_name,\n                t.max_member,\n                u.user_id,\n                u.name           AS user_name,\n                u.email          AS user_email,\n                u.nim,\n                u.prodi,\n                u.status         AS user_status\n            FROM team_members tm\n            JOIN teams t ON tm.team_id = t.team_id\n            JOIN users  u ON tm.user_id  = u.user_id\n        ");

        DB::statement("\n            CREATE OR REPLACE VIEW view_teams AS\n            SELECT\n                t.team_id,\n                t.team_name,\n                t.competition_name,\n                t.description,\n                t.max_member,\n                t.status,\n                t.competition_level,\n                t.achievement_rank,\n                t.photo_activity,\n                t.photo_certificate,\n                t.created_at,\n                t.updated_at,\n                t.deleted_at,\n                (\n                    SELECT COUNT(*)\n                    FROM team_members tm\n                    WHERE tm.team_id = t.team_id\n                      AND tm.status  = 'active'\n                )                   AS current_member_count,\n                (\n                    SELECT COUNT(*)\n                    FROM team_members tm\n                    WHERE tm.team_id = t.team_id\n                      AND tm.status  = 'pending'\n                )                   AS pending_member_count,\n                (\n                    SELECT u.name\n                    FROM team_members tm\n                    JOIN users u ON tm.user_id = u.user_id\n                    WHERE tm.team_id = t.team_id\n                      AND tm.role    = 'leader'\n                    LIMIT 1\n                )                   AS leader_name,\n                (\n                    SELECT u.user_id\n                    FROM team_members tm\n                    JOIN users u ON tm.user_id = u.user_id\n                    WHERE tm.team_id = t.team_id\n                      AND tm.role    = 'leader'\n                    LIMIT 1\n                )                   AS leader_id\n            FROM teams t\n            WHERE t.deleted_at IS NULL\n        ");

        DB::statement("\n            CREATE OR REPLACE VIEW view_events AS\n            SELECT\n                e.event_id,\n                e.title,\n                e.description,\n                e.start_date,\n                e.end_date,\n                e.location,\n                e.poster,\n                e.status,\n                e.created_at,\n                e.updated_at,\n                e.deleted_at,\n                ec.category_id,\n                ec.name_category,\n                u.user_id         AS creator_id,\n                u.name            AS creator_name,\n                u.email           AS creator_email,\n                (\n                    SELECT COUNT(*)\n                    FROM event_likes el\n                    WHERE el.event_id = e.event_id\n                )                 AS total_likes\n            FROM events e\n            JOIN event_categories ec ON e.category_id = ec.category_id\n            JOIN users u             ON e.created_by  = u.user_id\n        ");

        Schema::table('team_members', function (Blueprint $table) {
            if (Schema::hasColumn('team_members', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });

        Schema::table('teams', function (Blueprint $table) {
            if (Schema::hasColumn('teams', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });

        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};
