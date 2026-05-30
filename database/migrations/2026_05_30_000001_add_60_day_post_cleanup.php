<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            DROP EVENT IF EXISTS cleanup_old_events_after_60_days
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE EVENT cleanup_old_events_after_60_days
            ON SCHEDULE EVERY 1 DAY
            STARTS CURRENT_TIMESTAMP
            DO
                UPDATE events
                SET deleted_at = NOW()
                WHERE deleted_at IS NULL
                  AND created_at IS NOT NULL
                  AND created_at < (NOW() - INTERVAL 60 DAY)
        SQL);

        DB::unprepared(<<<'SQL'
            DROP EVENT IF EXISTS cleanup_old_lostfound_after_60_days
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE EVENT cleanup_old_lostfound_after_60_days
            ON SCHEDULE EVERY 1 DAY
            STARTS CURRENT_TIMESTAMP
            DO
                UPDATE lostfound_items
                SET deleted_at = NOW()
                WHERE deleted_at IS NULL
                  AND created_at IS NOT NULL
                  AND created_at < (NOW() - INTERVAL 60 DAY)
        SQL);

        $this->createViewEvents(true);
    }

    public function down(): void
    {
        DB::unprepared('DROP EVENT IF EXISTS cleanup_old_events_after_60_days');
        DB::unprepared('DROP EVENT IF EXISTS cleanup_old_lostfound_after_60_days');

        $this->createViewEvents(false);
    }

    private function createViewEvents(bool $filterDeleted): void
    {
        $whereClause = $filterDeleted ? 'WHERE e.deleted_at IS NULL' : '';

        DB::statement("\n            CREATE OR REPLACE VIEW view_events AS\n            SELECT\n                e.event_id,\n                e.title,\n                e.description,\n                e.start_date,\n                e.end_date,\n                e.location,\n                e.poster,\n                e.status,\n                e.rejection_reason,\n                e.created_at,\n                e.updated_at,\n                e.deleted_at,\n                ec.category_id,\n                ec.name_category,\n                u.user_id         AS creator_id,\n                u.name            AS creator_name,\n                u.email           AS creator_email,\n                (\n                    SELECT COUNT(*)\n                    FROM event_likes el\n                    WHERE el.event_id = e.event_id\n                )                 AS total_likes\n            FROM events e\n            JOIN event_categories ec ON e.category_id = ec.category_id\n            JOIN users u             ON e.created_by  = u.user_id\n            {$whereClause}\n        ");
    }
};
