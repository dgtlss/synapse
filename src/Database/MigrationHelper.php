<?php

namespace Dgtlss\Synapse\Database;

use Illuminate\Database\Schema\Blueprint;

class MigrationHelper
{
    /**
     * Add a vector column to the table.
     *
     * @param Blueprint $table
     * @param string $column
     * @param int $dimensions
     * @return void
     */
    public static function addVectorColumn(Blueprint $table, string $column, int $dimensions = 1536): void
    {
        // For PostgreSQL with pgvector extension
        if (config('database.default') === 'pgsql') {
            $table->vector($column, $dimensions)->nullable();
            return;
        }

        // For MySQL and MariaDB, we'll use JSON column as fallback
        if (config('database.default') === 'mysql' || config('database.default') === 'mariadb') {
            $table->json($column)->nullable();
            return;
        }

        // For SQLite, we'll use TEXT column as fallback
        if (config('database.default') === 'sqlite') {
            $table->text($column)->nullable();
            return;
        }

        // Default fallback
        $table->text($column)->nullable();
    }

    /**
     * Create a migration for adding vector columns to an existing table.
     *
     * @param string $tableName
     * @param array $vectorColumns
     * @return string
     */
    public static function createVectorMigration(string $tableName, array $vectorColumns): string
    {
        $migrationName = 'add_vector_columns_to_' . $tableName . '_table';
        $className = 'AddVectorColumnsTo' . ucfirst($tableName) . 'Table';
        
        $migrationContent = "<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Dgtlss\\Synapse\\Database\\MigrationHelper;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('{$tableName}', function (Blueprint \$table) {
";

        foreach ($vectorColumns as $column => $dimensions) {
            $migrationContent .= "            MigrationHelper::addVectorColumn(\$table, '{$column}', {$dimensions});\n";
        }

        $migrationContent .= "        });
    }

    public function down(): void
    {
        Schema::table('{$tableName}', function (Blueprint \$table) {
";

        foreach ($vectorColumns as $column => $dimensions) {
            $migrationContent .= "            \$table->dropColumn('{$column}');\n";
        }

        $migrationContent .= "        });
    }
};";

        return $migrationContent;
    }
}