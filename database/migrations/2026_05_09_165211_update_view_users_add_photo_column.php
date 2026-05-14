<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW view_users AS
            SELECT
                user_id,
                name,
                email,
                role,
                nim,
                prodi,
                photo,
                status,
                created_at
            FROM users
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
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
    }
};
