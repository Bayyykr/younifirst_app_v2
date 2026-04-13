<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Daftar semua view yang dibuat:
     *
     * 1. view_users          - Data user tanpa kolom sensitif (password, photo)
     * 2. view_events         - Event lengkap dengan nama kategori & nama pembuat
     * 3. view_event_likes    - Like event dengan info user & event
     * 4. view_teams          - Tim dengan jumlah member aktif & nama leader
     * 5. view_team_members   - Member tim dengan detail user & tim
     * 6. view_announcements  - Pengumuman dengan nama pembuat
     * 7. view_lostfound      - Barang hilang/temuan dengan nama status & nama pelapor
     * 8. view_lostfound_comments - Komentar lost & found dengan info user & item
     */
    public function up(): void
    {
        // ─────────────────────────────────────────────
        //  1. VIEW USERS
        //     Menampilkan data user tanpa kolom sensitif
        // ─────────────────────────────────────────────
        DB::statement("
            CREATE OR REPLACE VIEW view_users AS
            SELECT
                user_id,
                name,
                email,
                role,
                nim,
                prodi,
                status,
                created_at
            FROM users
        ");

        // ─────────────────────────────────────────────
        //  2. VIEW EVENTS
        //     Event + nama kategori + nama & email pembuat
        // ─────────────────────────────────────────────
        DB::statement("
            CREATE OR REPLACE VIEW view_events AS
            SELECT
                e.event_id,
                e.title,
                e.description,
                e.start_date,
                e.end_date,
                e.location,
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

        // ─────────────────────────────────────────────
        //  3. VIEW EVENT LIKES
        //     Like beserta detail user & judul event
        // ─────────────────────────────────────────────
        DB::statement("
            CREATE OR REPLACE VIEW view_event_likes AS
            SELECT
                el.like_id,
                el.created_at    AS liked_at,
                e.event_id,
                e.title          AS event_title,
                e.status         AS event_status,
                u.user_id,
                u.name           AS user_name,
                u.email          AS user_email,
                u.nim,
                u.prodi
            FROM event_likes el
            JOIN events e ON el.event_id = e.event_id
            JOIN users  u ON el.user_id  = u.user_id
        ");

        // ─────────────────────────────────────────────
        //  4. VIEW TEAMS
        //     Tim + jumlah member aktif + nama leader
        // ─────────────────────────────────────────────
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

        // ─────────────────────────────────────────────
        //  5. VIEW TEAM MEMBERS
        //     Member + detail user + detail tim
        // ─────────────────────────────────────────────
        DB::statement("
            CREATE OR REPLACE VIEW view_team_members AS
            SELECT
                tm.member_id,
                tm.role          AS member_role,
                tm.status        AS member_status,
                t.team_id,
                t.team_name,
                t.competition_name,
                t.max_member,
                u.user_id,
                u.name           AS user_name,
                u.email          AS user_email,
                u.nim,
                u.prodi,
                u.status         AS user_status
            FROM team_members tm
            JOIN teams t ON tm.team_id = t.team_id
            JOIN users  u ON tm.user_id  = u.user_id
        ");

        // ─────────────────────────────────────────────
        //  6. VIEW ANNOUNCEMENTS
        //     Pengumuman + nama & email pembuat
        // ─────────────────────────────────────────────
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

        // ─────────────────────────────────────────────
        //  7. VIEW LOSTFOUND ITEMS
        //     Barang hilang/temuan + nama status + info pelapor
        // ─────────────────────────────────────────────
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

        // ─────────────────────────────────────────────
        //  8. VIEW LOSTFOUND COMMENTS
        //     Komentar + detail item + detail user
        // ─────────────────────────────────────────────
        DB::statement("
            CREATE OR REPLACE VIEW view_lostfound_comments AS
            SELECT
                lc.comment_id,
                lc.comment,
                lc.created_at,
                lc.update_at,
                li.lostfound_id,
                li.item_name,
                li.location       AS item_location,
                u.user_id,
                u.name            AS commenter_name,
                u.email           AS commenter_email,
                u.nim,
                u.prodi
            FROM lostfound_comments lc
            JOIN lostfound_items li ON lc.lostfound_id = li.lostfound_id
            JOIN users           u  ON lc.user_id      = u.user_id
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS view_lostfound_comments');
        DB::statement('DROP VIEW IF EXISTS view_lostfound');
        DB::statement('DROP VIEW IF EXISTS view_announcements');
        DB::statement('DROP VIEW IF EXISTS view_team_members');
        DB::statement('DROP VIEW IF EXISTS view_teams');
        DB::statement('DROP VIEW IF EXISTS view_event_likes');
        DB::statement('DROP VIEW IF EXISTS view_events');
        DB::statement('DROP VIEW IF EXISTS view_users');
    }
};
