<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            if (!Schema::hasColumn('teams', 'competition_level')) {
                $table->enum('competition_level', ['kampus', 'regional', 'nasional', 'internasional'])
                    ->nullable()
                    ->after('description')
                    ->comment('Tingkat lomba: kampus, regional, nasional, internasional');
            }

            if (!Schema::hasColumn('teams', 'achievement_rank')) {
                $table->string('achievement_rank', 50)
                    ->nullable()
                    ->after('competition_level')
                    ->comment('Juara ke-berapa, contoh: Juara 1, Juara 2, Finalis, dll');
            }

            if (!Schema::hasColumn('teams', 'photo_activity')) {
                $table->string('photo_activity', 500)
                    ->nullable()
                    ->after('achievement_rank')
                    ->comment('Path foto kegiatan lomba');
            }

            if (!Schema::hasColumn('teams', 'photo_certificate')) {
                $table->string('photo_certificate', 500)
                    ->nullable()
                    ->after('photo_activity')
                    ->comment('Path foto sertifikat pemenang');
            }
        });

        // Update VIEW view_teams untuk menyertakan kolom baru
        DB::statement("
            CREATE OR REPLACE VIEW view_teams AS
            SELECT
                t.team_id,
                t.team_name,
                t.competition_name,
                t.description,
                t.max_member,
                t.status,
                t.competition_level,
                t.achievement_rank,
                t.photo_activity,
                t.photo_certificate,
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
                    SELECT COUNT(*)
                    FROM team_members tm
                    WHERE tm.team_id = t.team_id
                      AND tm.status  = 'pending'
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
            WHERE t.deleted_at IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $colsToDrop = [];
            if (Schema::hasColumn('teams', 'competition_level')) $colsToDrop[] = 'competition_level';
            if (Schema::hasColumn('teams', 'achievement_rank')) $colsToDrop[] = 'achievement_rank';
            if (Schema::hasColumn('teams', 'photo_activity')) $colsToDrop[] = 'photo_activity';
            if (Schema::hasColumn('teams', 'photo_certificate')) $colsToDrop[] = 'photo_certificate';
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });

        // Revert view ke versi sebelumnya
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
                    SELECT COUNT(*)
                    FROM team_members tm
                    WHERE tm.team_id = t.team_id
                      AND tm.status  = 'pending'
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
            WHERE t.deleted_at IS NULL
        ");
    }
};
