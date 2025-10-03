<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('profiles', 'city')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->string('city', 50)->nullable()->after('region');
            });
        }

        $this->ensureLicenseTypeIsText();
    }

    public function down(): void
    {
        if (Schema::hasColumn('profiles', 'city')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->dropColumn('city');
            });
        }

        $this->revertLicenseTypeColumn();
    }

    private function ensureLicenseTypeIsText(): void
    {
        if (! Schema::hasColumn('profiles', 'license_type')) {
            return;
        }

        $columnType = $this->getLicenseTypeColumnType();
        if ($columnType && in_array($columnType, ['text', 'mediumtext', 'longtext'], true)) {
            return;
        }

        $driver = DB::getDriverName();

        match ($driver) {
            'mysql' => DB::statement('ALTER TABLE profiles MODIFY license_type TEXT NULL'),
            'pgsql' => DB::statement('ALTER TABLE profiles ALTER COLUMN license_type TYPE TEXT'),
            default => null,
        };
    }

    private function revertLicenseTypeColumn(): void
    {
        if (! Schema::hasColumn('profiles', 'license_type')) {
            return;
        }

        $driver = DB::getDriverName();

        match ($driver) {
            'mysql' => DB::statement('ALTER TABLE profiles MODIFY license_type VARCHAR(255) NULL'),
            'pgsql' => DB::statement('ALTER TABLE profiles ALTER COLUMN license_type TYPE VARCHAR(255)'),
            default => null,
        };
    }

    private function getLicenseTypeColumnType(): ?string
    {
        $driver = DB::getDriverName();

        return match ($driver) {
            'sqlite' => $this->getSqliteColumnType(),
            'mysql' => $this->getMysqlColumnType(),
            'pgsql' => $this->getPostgresColumnType(),
            default => null,
        };
    }

    private function getSqliteColumnType(): ?string
    {
        $columns = DB::select("PRAGMA table_info('profiles')");

        foreach ($columns as $column) {
            if (($column->name ?? null) === 'license_type') {
                return strtolower($column->type ?? '');
            }
        }

        return null;
    }

    private function getMysqlColumnType(): ?string
    {
        $result = DB::selectOne(<<<'SQL'
            SELECT DATA_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'profiles'
              AND COLUMN_NAME = 'license_type'
        SQL);

        return $result ? strtolower($result->DATA_TYPE ?? '') : null;
    }

    private function getPostgresColumnType(): ?string
    {
        $result = DB::selectOne(<<<'SQL'
            SELECT data_type
            FROM information_schema.columns
            WHERE table_schema = current_schema()
              AND table_name = 'profiles'
              AND column_name = 'license_type'
        SQL);

        return $result ? strtolower($result->data_type ?? '') : null;
    }
};
