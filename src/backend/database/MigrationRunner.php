<?php

namespace database;

use Medoo\Medoo;

class MigrationRunner
{
    private Medoo $database;
    private string $migrationsPath;

    public function __construct(string $migrationsPath = __DIR__ . '/migrations')
    {
        $this->database = Connection::getInstance();
        $this->migrationsPath = $migrationsPath;
    }

    public function run(): void
    {
        // Create migrations table if it doesn't exist
        $this->createMigrationsTable();

        // Get all migration files
        $files = glob("{$this->migrationsPath}/*.sql");
        sort($files);

        // Get executed migrations
        $executedMigrations = $this->getExecutedMigrations();

        foreach ($files as $file) {
            $migrationName = basename($file);
            
            if (!in_array($migrationName, $executedMigrations)) {
                $sql = file_get_contents($file);
                
                try {
                    $this->database->query($sql);
                    $this->recordMigration($migrationName);
                    error_log("INFO: Executed migration: $migrationName");
                } catch (\Exception $e) {
                    error_log("ERROR: Failed executing migration $migrationName: " . $e->getMessage());
                    throw $e; // Re-throw the exception to handle it at a higher level
                }
            } else {
                error_log("DEBUG: Migration $migrationName already executed");
            }
        }
    }

    private function createMigrationsTable(): void
    {
        $this->database->query("CREATE DATABASE IF NOT EXISTS yalp_db");
        $this->database->query("USE yalp_db");
        $this->database->query("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255),
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    private function getExecutedMigrations(): array
    {
        $result = $this->database->select('migrations', ['migration']);
        return array_column($result, 'migration');
    }

    private function recordMigration(string $migrationName): void
    {
        $this->database->insert('migrations', [
            'migration' => $migrationName
        ]);
    }
}