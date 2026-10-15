<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

final class ImportRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function createQueued(string $id): void
    {
        $this->pdo->prepare("INSERT INTO imports (id, status) VALUES (:id, 'queued')")
            ->execute(['id' => $id]);
    }

    public function markQueueFailed(string $id): void
    {
        $this->pdo->prepare("UPDATE imports SET status = 'failed', errors = :errors WHERE id = :id")
            ->execute(['id' => $id, 'errors' => json_encode([['message' => 'Queue unavailable']])]);
    }

    public function markProcessing(string $id, int $total): void
    {
        $this->pdo->prepare(
            "UPDATE imports SET status = 'processing', total = :total, processed = 0 WHERE id = :id",
        )->execute(['id' => $id, 'total' => $total]);
    }

    public function incrementProcessed(string $id): void
    {
        $this->pdo->prepare('UPDATE imports SET processed = processed + 1 WHERE id = :id')
            ->execute(['id' => $id]);
    }

    public function complete(string $id, array $errors): void
    {
        $this->pdo->prepare(
            'UPDATE imports
             SET status = :status, errors = :errors, finished_at = NOW()
             WHERE id = :id',
        )->execute([
            'id' => $id,
            'status' => $errors ? 'completed_with_errors' : 'completed',
            'errors' => json_encode($errors, JSON_THROW_ON_ERROR),
        ]);
    }

    public function find(string $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM imports WHERE id = :id');
        $statement->execute(['id' => $id]);
        $import = $statement->fetch(PDO::FETCH_ASSOC);

        return $import === false ? null : $import;
    }

    /** @return list<array<string, mixed>> */
    public function listTasks(int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));
        $statement = $this->pdo->query(
            'SELECT id, status, total, processed, errors, created_at, finished_at
             FROM imports ORDER BY created_at DESC LIMIT ' . $limit,
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countTasks(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM imports')->fetchColumn();
    }
}