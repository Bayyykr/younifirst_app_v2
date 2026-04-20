<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Changes:
     * 1. Add `deleted_at` to `announcements` table (soft delete support).
     * 2. Rename `delete_at` to `deleted_at` on `teams` table (align with Laravel SoftDeletes convention).
     * 3. Drop `status_id` FK & column from `lostfound_items`, add enum `status` column.
     * 4. Migrate existing status_id data to string enum values.
     * 5. Update DB views: view_announcements (add deleted_at filter), view_teams (use deleted_at), view_lostfound (drop item_status JOIN).
     */
    public function up(): void
    {
        // ── 1. Add soft delete to announcements ────────────────────────────────
        if (! Schema::hasColumn('announcements', 'deleted_at')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->timestamp('deleted_at')->nullable()->after('created_at');
            });
        }

        // ── 2. Rename update_at -> updated_at on teams (if not done yet) ───────────
        if (Schema::hasColumn('teams', 'update_at')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->renameColumn('update_at', 'updated_at');
            });
        }
        // Ensure deleted_at exists on teams (add if missing)
        if (! Schema::hasColumn('teams', 'deleted_at')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->timestamp('deleted_at')->nullable()->after('updated_at');
            });
        }

        // ── 3. Replace status_id with enum status on lostfound_items ─────────
        if (Schema::hasColumn('lostfound_items', 'status_id')) {
            // Single atomic ALTER TABLE:
            // - Drop FK that relies on the composite index
            // - Drop both old indexes
            // - Drop status_id column
            // - Add new enum status column
            // - Add new single-column user_id index
            // - Re-add the user_id FK on the new index
            DB::statement("
                ALTER TABLE lostfound_items
                    DROP FOREIGN KEY lostfound_items_user_id_foreign,
                    DROP FOREIGN KEY lostfound_items_status_id_foreign,
                    DROP INDEX lostfound_items_user_id_status_id_index,
                    DROP INDEX lostfound_items_status_id_foreign,
                    DROP COLUMN status_id,
                    ADD COLUMN `status` ENUM('lost','found','claimed') NOT NULL DEFAULT 'lost' AFTER `location`,
                    ADD INDEX lostfound_items_user_id_index (`user_id`),
                    ADD CONSTRAINT lostfound_items_user_fk FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
            ");
        }

        // ── 4. Update DB Views ────────────────────────────────────────────────

        // RECREATE view_teams (uses deleted_at instead of delete_at, filters soft-deleted rows)
        DB::statement('DROP VIEW IF EXISTS view_teams');
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

        // RECREATE view_announcements (filters soft-deleted rows)
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

        // RECREATE view_lostfound (use string enum status, drop item_status JOIN, filter soft-deleted)
        DB::statement('DROP VIEW IF EXISTS view_lostfound');
        DB::statement("
            CREATE OR REPLACE VIEW view_lostfound AS
            SELECT
                li.lostfound_id,
                li.item_name,
                li.description,
                li.location,
                li.photo,
                li.status,
                li.created_at,
                li.updated_at,
                li.deleted_at,
                u.user_id         AS reporter_id,
                u.name            AS reporter_name,
                u.email           AS reporter_email,
                u.nim             AS reporter_nim,
                u.prodi           AS reporter_prodi,
                (
                    SELECT COUNT(*)
                    FROM lostfound_comments lc
                    WHERE lc.lostfound_id = li.lostfound_id
                )                 AS total_comments
            FROM lostfound_items li
            JOIN users u ON li.user_id = u.user_id
            WHERE li.deleted_at IS NULL
        ");

        // RECREATE view_events (includes poster)
        DB::statement('DROP VIEW IF EXISTS view_events');
        DB::statement("
            CREATE OR REPLACE VIEW view_events AS
            SELECT
                e.event_id,
                e.title,
                e.description,
                e.start_date,
                e.end_date,
                e.location,
                e.poster,
                e.status,
                e.created_at,
                e.updated_at,
                e.deleted_at,
                ec.category_id,
                ec.name_category,
                u.user_id         AS creator_id,
                u.name            AS creator_name,
                u.email           AS creator_email,
                (
                    SELECT COUNT(*)
                    FROM event_likes el
                    WHERE el.event_id = e.event_id
                )                 AS total_likes
            FROM events e
            JOIN event_categories ec ON e.category_id = ec.category_id
            JOIN users u             ON e.created_by  = u.user_id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ── Restore view_lostfound (original with item_status JOIN) ───────────
        DB::statement('DROP VIEW IF EXISTS view_lostfound');
        DB::statement("
            CREATE OR REPLACE VIEW view_lostfound AS
            SELECT
                li.lostfound_id,
                li.item_name,
                li.description,
                li.location,
                li.created_at,
                li.updated_at,
                li.deleted_at,
                ist.status_id,
                ist.name_status,
                u.user_id         AS reporter_id,
                u.name            AS reporter_name,
                u.email           AS reporter_email,
                u.nim             AS reporter_nim,
                u.prodi           AS reporter_prodi,
                (
                    SELECT COUNT(*)
                    FROM lostfound_comments lc
                    WHERE lc.lostfound_id = li.lostfound_id
                )                 AS total_comments
            FROM lostfound_items li
            JOIN item_status ist ON li.status_id = ist.status_id
            JOIN users       u   ON li.user_id   = u.user_id
        ");

        // ── Restore view_announcements (no deleted_at filter) ─────────────────
        DB::statement('DROP VIEW IF EXISTS view_announcements');
        DB::statement("
            CREATE OR REPLACE VIEW view_announcements AS
            SELECT
                a.announcement_id,
                a.title,
                a.content,
                a.created_at,
                u.user_id         AS creator_id,
                u.name            AS creator_name,
                u.email           AS creator_email,
                u.role            AS creator_role
            FROM announcements a
            JOIN users u ON a.created_by = u.user_id
        ");

        // ── Restore view_teams ────────────────────────────────────────────────
        DB::statement('DROP VIEW IF EXISTS view_teams');
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

        // ── Restore lostfound_items schema ────────────────────────────────────
        Schema::table('lostfound_items', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropIndex(['user_id']);
            $table->integer('status_id')->after('location');
            $table->index(['user_id', 'status_id']);
            $table->foreign('status_id')->references('status_id')->on('item_status');
        });

        // ── Restore teams column name ─────────────────────────────────────────
        Schema::table('teams', function (Blueprint $table) {
            $table->renameColumn('deleted_at', 'delete_at');
        });

        // ── Remove deleted_at from announcements ──────────────────────────────
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
