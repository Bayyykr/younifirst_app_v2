<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Patch: Tambah kolom pending_member_count ke view_teams
 * agar tidak perlu query tambahan di controller/route.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW view_teams AS
            SELECT
                t.team_id,
                t.team_name,
                t.competition_name,
                t.description,
                t.max_member,
                t.created_at,
                t.update_at,
                t.delete_at,
                (
                    SELECT COUNT(*)
                    FROM team_members tm
                    WHERE tm.team_id = t.team_id
                      AND tm.status  = 'active'
                )                   AS current_member_count,
                (
                    SELECT COUNT(*)
                    FROM team_members tm
                    WHERE tm.team_id = t.team_id
                      AND tm.status  = 'inactive'
                )                   AS pending_member_count,
                (
                    SELECT u.name
                    FROM team_members tm
                    JOIN users u ON tm.user_id = u.user_id
                    WHERE tm.team_id = t.team_id
                      AND tm.role    = 'leader'
                    LIMIT 1
                )                   AS leader_name,
                (
                    SELECT u.user_id
                    FROM team_members tm
                    JOIN users u ON tm.user_id = u.user_id
                    WHERE tm.team_id = t.team_id
                      AND tm.role    = 'leader'
                    LIMIT 1
                )                   AS leader_id
            FROM teams t
        ");
    }

    public function down(): void
    {
        // Rollback ke versi sebelumnya (tanpa pending_member_count)
        DB::statement("
            CREATE OR REPLACE VIEW view_teams AS
            SELECT
                t.team_id,
                t.team_name,
                t.competition_name,
                t.description,
                t.max_member,
                t.created_at,
                t.update_at,
                t.delete_at,
                (
                    SELECT COUNT(*)
                    FROM team_members tm
                    WHERE tm.team_id = t.team_id
                      AND tm.status  = 'active'
                )                   AS current_member_count,
                (
                    SELECT u.name
                    FROM team_members tm
                    JOIN users u ON tm.user_id = u.user_id
                    WHERE tm.team_id = t.team_id
                      AND tm.role    = 'leader'
                    LIMIT 1
                )                   AS leader_name,
                (
                    SELECT u.user_id
                    FROM team_members tm
                    JOIN users u ON tm.user_id = u.user_id
                    WHERE tm.team_id = t.team_id
                      AND tm.role    = 'leader'
                    LIMIT 1
                )                   AS leader_id
            FROM teams t
        ");
    }
};
