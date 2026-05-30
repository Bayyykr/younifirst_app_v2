<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->createViewEvents(true, true);
    }

    public function down(): void
    {
        $this->createViewEvents(true, false);
    }

    private function createViewEvents(bool $filterDeleted, bool $includeLikesCount): void
    {
        $whereClause = $filterDeleted ? 'WHERE e.deleted_at IS NULL' : '';
        $likesCountSelect = $includeLikesCount
            ? ",\n                (\n                    SELECT COUNT(*)\n                    FROM event_likes el\n                    WHERE el.event_id = e.event_id\n                )                 AS likes_count"
            : '';

        DB::statement("\n            CREATE OR REPLACE VIEW view_events AS\n            SELECT\n                e.event_id,\n                e.title,\n                e.description,\n                e.start_date,\n                e.end_date,\n                e.location,\n                e.poster,\n                e.status,\n                e.rejection_reason,\n                e.created_at,\n                e.updated_at,\n                e.deleted_at,\n                ec.category_id,\n                ec.name_category,\n                u.user_id         AS creator_id,\n                u.name            AS creator_name,\n                u.email           AS creator_email,\n                (\n                    SELECT COUNT(*)\n                    FROM event_likes el\n                    WHERE el.event_id = e.event_id\n                )                 AS total_likes\n                {$likesCountSelect}\n            FROM events e\n            JOIN event_categories ec ON e.category_id = ec.category_id\n            JOIN users u             ON e.created_by  = u.user_id\n            {$whereClause}\n        ");
    }
};
