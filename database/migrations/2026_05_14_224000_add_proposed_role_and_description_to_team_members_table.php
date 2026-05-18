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
        Schema::table('team_members', function (Blueprint $table) {
            $table->string('proposed_role', 100)->nullable()->after('portfolio');
            $table->text('description')->nullable()->after('proposed_role');
        });

        DB::statement("
            CREATE OR REPLACE VIEW view_team_members AS
            SELECT
                tm.member_id,
                tm.role          AS member_role,
                tm.status        AS member_status,
                tm.portfolio,
                tm.proposed_role,
                tm.description,
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW view_team_members AS
            SELECT
                tm.member_id,
                tm.role          AS member_role,
                tm.status        AS member_status,
                tm.portfolio,
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

        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn(['proposed_role', 'description']);
        });
    }
};
