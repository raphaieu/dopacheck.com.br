<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('oauth_connections')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        $hasToken = Schema::hasColumn('oauth_connections', 'token');
        $hasRefreshToken = Schema::hasColumn('oauth_connections', 'refresh_token');

        // SQLite não aplica limites reais de VARCHAR como MySQL/MariaDB.
        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'mysql') {
            if ($hasToken) {
                DB::statement('ALTER TABLE oauth_connections MODIFY token TEXT NULL');
            }
            if ($hasRefreshToken) {
                DB::statement('ALTER TABLE oauth_connections MODIFY refresh_token TEXT NULL');
            }
            return;
        }

        if ($driver === 'pgsql') {
            if ($hasToken) {
                DB::statement('ALTER TABLE oauth_connections ALTER COLUMN token TYPE text');
            }
            if ($hasRefreshToken) {
                DB::statement('ALTER TABLE oauth_connections ALTER COLUMN refresh_token TYPE text');
            }
            return;
        }

        if ($driver === 'sqlsrv') {
            if ($hasToken) {
                DB::statement('ALTER TABLE oauth_connections ALTER COLUMN token NVARCHAR(MAX) NULL');
            }
            if ($hasRefreshToken) {
                DB::statement('ALTER TABLE oauth_connections ALTER COLUMN refresh_token NVARCHAR(MAX) NULL');
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('oauth_connections')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        $hasToken = Schema::hasColumn('oauth_connections', 'token');
        $hasRefreshToken = Schema::hasColumn('oauth_connections', 'refresh_token');

        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'mysql') {
            if ($hasToken) {
                DB::statement('ALTER TABLE oauth_connections MODIFY token VARCHAR(255) NULL');
            }
            if ($hasRefreshToken) {
                DB::statement('ALTER TABLE oauth_connections MODIFY refresh_token VARCHAR(255) NULL');
            }
            return;
        }

        if ($driver === 'pgsql') {
            if ($hasToken) {
                DB::statement('ALTER TABLE oauth_connections ALTER COLUMN token TYPE varchar(255)');
            }
            if ($hasRefreshToken) {
                DB::statement('ALTER TABLE oauth_connections ALTER COLUMN refresh_token TYPE varchar(255)');
            }
            return;
        }

        if ($driver === 'sqlsrv') {
            if ($hasToken) {
                DB::statement('ALTER TABLE oauth_connections ALTER COLUMN token NVARCHAR(255) NULL');
            }
            if ($hasRefreshToken) {
                DB::statement('ALTER TABLE oauth_connections ALTER COLUMN refresh_token NVARCHAR(255) NULL');
            }
        }
    }
};

