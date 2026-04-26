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
        if (!Schema::hasColumn('teams', 'status')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('max_member');
            });
        }

        // Update VIEW view_teams to include status
        DB::statement("
            CREATE OR REPLACE VIEW view_teams AS
            SELECT
                t.team_id,
                t.team_name,
                t.competition_name,
                t.description,
                t.max_member,
                t.status,
                t.created_at,
                t.updated_at,
                t.deleted_at,
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
            WHERE t.deleted_at IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Revert view_teams (remove status)
        DB::statement("
            CREATE OR REPLACE VIEW view_teams AS
            SELECT
                t.team_id,
                t.team_name,
                t.competition_name,
                t.description,
                t.max_member,
                t.created_at,
                t.updated_at,
                t.deleted_at,
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
            WHERE t.deleted_at IS NULL
        ");
    }
};
