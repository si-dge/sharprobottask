<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class DatabaseResetRepository
{
    public function __construct(private PDO $pdo) {}

    public function truncate(): void
    {
        $this->pdo->beginTransaction();

        try {
            $this->pdo->exec(
                'TRUNCATE TABLE product_images, product_attributes, products, imports '
                . 'RESTART IDENTITY CASCADE',
            );
            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
