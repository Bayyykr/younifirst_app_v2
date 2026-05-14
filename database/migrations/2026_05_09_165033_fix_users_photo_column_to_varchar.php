<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Change users.photo from binary/blob → varchar(255)
     * so file-path strings can be stored and read correctly.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE users MODIFY COLUMN photo VARCHAR(255) NULL DEFAULT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users MODIFY COLUMN photo BLOB NULL DEFAULT NULL');
    }
};
