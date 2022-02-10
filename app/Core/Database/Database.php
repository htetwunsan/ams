<?php

namespace App\Core\Database;

use DirectoryIterator;
use PDO;
use PDOException;

class Database
{
    public PDO $pdo;

    public function __construct()
    {
        try {
            $host = $_ENV['host'] ?? 'mysql';
            $this->pdo = new PDO("mysql:host=$host;port=3306;dbname=my_db", 'root', 'root');
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), $e->getCode());
        }
    }

    public function applyMigrations()
    {
        $this->createMigrationsTable();

        $newMigrations = [];

        $appliedMigrations = $this->getAppliedMigrations();

        $iterator = new DirectoryIterator(base_path() . '/app/Migrations');

        $migrations = [];

        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                $class = pathinfo($fileinfo->getFilename(), PATHINFO_FILENAME);
                $exploded = explode('_', $class);
                $index = (int) end($exploded);
                $migrations[$index] = $class;
            }
        }
        $migrations = array_diff($migrations, $appliedMigrations);
        ksort($migrations);

        $app = app();

        foreach ($migrations as $class) {
            $this->log("Applying migration $class");
            $app->get("App\\Migrations\\$class")->up();
            $this->log("Applied migraton $class");

            $newMigrations[] = $class;
        }

        if ($newMigrations) {
            $this->saveMigrations($newMigrations);
        } else {
            $this->log("All migrations are applied.");
        }
    }

    private function createMigrationsTable()
    {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS migrations(
            id INT UNSIGNED AUTO_INCREMENT,
            migration VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
            )"
        );
    }

    private function getAppliedMigrations()
    {
        $statement = $this->pdo->prepare("SELECT migration FROM migrations");
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function saveMigrations(array $migrations)
    {
        $values = implode(",", array_map(fn ($migration) => "('$migration')", $migrations));
        $statement = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES $values");
        $statement->execute();
    }

    private function log($message)
    {
        echo '[' . date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL;
    }
}
