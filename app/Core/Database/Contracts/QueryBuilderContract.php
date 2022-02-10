<?php

namespace App\Core\Database\Contracts;

use PDO;

interface QueryBuilderContract
{
    public function withTransaction(callable $callback): void;

    public function select(string $table, array $columns = [], array $wheres = [], array $orders = [], int $limit = 1, int $offset = 0): array|bool;

    public function insert(string $table, array $columns): int;

    public function update(string $table, array $columns, array $wheres = [], bool $touch = false): int;

    public function upsert(string $table, array $columns, bool $touch = false): int;

    public function delete(string $table, array $wheres = []): int;

    public function getPdo(): PDO;
}
