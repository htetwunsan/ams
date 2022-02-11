<?php

namespace App\Core\Database;

use App\Core\Database\Contracts\QueryBuilderContract;
use PDO;
use PDOException;

class QueryBuilder implements QueryBuilderContract
{

    public function __construct(
        protected Database $db
    ) {
    }

    public function withTransaction(callable $callback): void
    {
        try {
            $this->getPdo()->beginTransaction();
            $callback($this);
            $this->getPdo()->commit();
        } catch (PDOException $e) {
            $this->getPdo()->rollBack();
            throw $e;
        }
    }

    public function select(string $table, array $columns = [], array $wheres = [], array $orders = [], int $limit = 1, int $offset = 0): array|bool
    {
        $columnNames = $columns ? implode(',', $columns) : '*';
        $query = "SELECT $columnNames FROM $table";
        if ($wheres) {
            $whereSets = implode(' AND ', array_map(fn ($key) => "$key=:where$key", array_keys($wheres)));
            $query .= " WHERE ($whereSets)";
        }
        if ($orders) {
            $orderClause = "";
            foreach ($orders as $col => $dir) {
                $orderClause .= "$col $dir,";
            }
            $orderClause = substr($orderClause, 0, -1);
            $query .= " ORDER BY $orderClause";
        }
        if ($limit > 0) {
            $query .= " LIMIT $limit";
        }
        if ($offset > 0) {
            $query .= " OFFSET $offset";
        }
        $stmt = $this->getPdo()->prepare($query);
        foreach ($wheres as $key => $value) {
            $type = $this->getPdoParam($value);
            $stmt->bindValue(":where$key", $value, $type);
        }
        $stmt->execute();
        if ($limit === 1) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(string $table, array $columns): int
    {
        $keys = array_keys($columns);
        $names = implode(',', $keys);
        $placeholders = implode(',', array_map(fn ($key) => ":$key", $keys));
        $query = "INSERT INTO $table ($names) VALUES ($placeholders)";


        $stmt = $this->getPdo()->prepare($query);
        foreach ($columns as $key => $value) {
            $type = $this->getPdoParam($value);
            $stmt->bindValue(":$key", $value, $type);
        }
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function update(string $table, array $columns, array $wheres = [], bool $touch = false): int
    {
        $sets = implode(',', array_map(fn ($key) => "$key=:$key", array_keys($columns)));
        if ($touch) {
            $sets .= ",updated_at=:updated_at";
        }
        $query = "UPDATE $table SET $sets";
        if ($wheres) {
            $whereSets = implode(',', array_map(fn ($key) => "$key=:where$key", array_keys($wheres)));
            $query .= " WHERE $whereSets";
        }


        $stmt = $this->getPdo()->prepare($query);
        foreach ($columns as $key => $value) {
            $type = match (true) {
                is_bool($value) => PDO::PARAM_BOOL,
                is_int($value) => PDO::PARAM_INT,
                default => PDO::PARAM_STR
            };
            $stmt->bindValue(":$key", $value, $type);
        }
        if ($touch) {
            $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'));
        }
        foreach ($wheres as $key => $value) {
            $type = $this->getPdoParam($value);
            $stmt->bindValue(":where$key", $value);
        }
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function upsert(string $table, array $columns, bool $touch = false): int
    {
        $keys = array_keys($columns);
        $names = implode(',', $keys);
        $insertPlaceholders = implode(',', array_map(fn ($key) => ":insert$key", $keys));
        $updatePlaceHolders = implode(',', array_map(fn ($key) => "$key=:update$key", $keys));
        if ($touch) {
            $updatePlaceHolders .= ",updated_at=:updated_at";
        }
        $query = "INSERT INTO $table ($names) VALUES ($insertPlaceholders) ON DUPLICATE KEY UPDATE $updatePlaceHolders";


        $stmt = $this->getPdo()->prepare($query);
        foreach ($columns as $key => $value) {
            $type = $this->getPdoParam($value);
            $stmt->bindValue(":insert$key", $value, $type);
            $stmt->bindValue(":update$key", $value, $type);
        }
        if ($touch) {
            $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'));
        }
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function delete(string $table, array $wheres = []): int
    {
        $query = "DELETE FROM $table";
        if ($wheres) {
            $whereSets = implode(',', array_map(fn ($key) => "$key=:where$key", array_keys($wheres)));
            $query .= " WHERE $whereSets";
        }
        $stmt = $this->getPdo()->prepare($query);


        foreach ($wheres as $key => $value) {
            $type = $this->getPdoParam($value);
            $stmt->bindValue(":where$key", $value, $type);
        }
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function getPdo(): PDO
    {
        return $this->db->pdo;
    }

    private function getPdoParam($value): int
    {
        return match (true) {
            is_bool($value) => PDO::PARAM_BOOL,
            is_int($value) => PDO::PARAM_INT,
            is_null($value) => PDO::PARAM_NULL,
            default => PDO::PARAM_STR
        };
    }

    public function __call(string $name, array $arguments): mixed
    {
        return call_user_func_array([$this->getPdo(), $name], $arguments);
    }
}
