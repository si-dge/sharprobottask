<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class ProductImportRepository
{
    public function __construct(private PDO $pdo) {}

    public function upsertProduct(array $product): string
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO products(id, external_code, name, description, price, discount)
             VALUES(:id, :external_code, :name, :description, :price, :discount)
             ON CONFLICT(external_code) DO UPDATE SET
               name = EXCLUDED.name,
               description = EXCLUDED.description,
               price = EXCLUDED.price,
               discount = EXCLUDED.discount,
               updated_at = NOW()
             RETURNING id',
        );
        $statement->execute($product);

        return (string) $statement->fetchColumn();
    }

    public function deleteAttributes(string $productId): void
    {
        $this->pdo->prepare('DELETE FROM product_attributes WHERE product_id = :id')
            ->execute(['id' => $productId]);
    }

    public function addAttribute(string $productId, string $key, string $value): void
    {
        $this->pdo->prepare(
            'INSERT INTO product_attributes(product_id, key, value)
             VALUES(:id, :key, :value)',
        )->execute([
            'id' => $productId,
            'key' => $key,
            'value' => $value,
        ]);
    }

    public function deleteImages(string $productId): void
    {
        $this->pdo->prepare('DELETE FROM product_images WHERE product_id = :id')
            ->execute(['id' => $productId]);
    }

    public function addImage(string $productId, string $url, string $path): void
    {
        $this->pdo->prepare(
            'INSERT INTO product_images(product_id, url, path)
             VALUES(:id, :url, :path)',
        )->execute([
            'id' => $productId,
            'url' => $url,
            'path' => $path,
        ]);
    }
}
