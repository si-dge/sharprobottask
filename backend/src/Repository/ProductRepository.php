<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class ProductRepository
{
    public function __construct(private PDO $pdo) {}

    public function paginate(array $query): array
    {
        $page = max(1, (int) ($query['page'] ?? 1));
        $limit = min(100, max(1, (int) ($query['limit'] ?? 20)));
        $where = [];
        $params = [];

        if (!empty($query['name'])) {
            $where[] = 'name ILIKE :name';
            $params['name'] = '%' . $query['name'] . '%';
        }
        if (isset($query['minPrice'])) {
            $where[] = 'price >= :min';
            $params['min'] = (float) $query['minPrice'];
        }
        if (isset($query['maxPrice'])) {
            $where[] = 'price <= :max';
            $params['max'] = (float) $query['maxPrice'];
        }

        $condition = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $count = $this->pdo->prepare("SELECT COUNT(*) FROM products $condition");
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $statement = $this->pdo->prepare(
            "SELECT id, external_code, name, description, price, discount FROM products $condition
             ORDER BY name LIMIT :limit OFFSET :offset",
        );
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', ($page - 1) * $limit, PDO::PARAM_INT);
        $statement->execute();

        return [
            'items' => $statement->fetchAll(PDO::FETCH_ASSOC),
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / $limit),
        ];
    }

    public function findWithRelations(string $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM products WHERE id = :id');
        $statement->execute(['id' => $id]);
        $product = $statement->fetch(PDO::FETCH_ASSOC);
        if ($product === false) {
            return null;
        }

        foreach (['product_attributes' => 'attributes', 'product_images' => 'images'] as $table => $key) {
            $related = $this->pdo->prepare("SELECT * FROM $table WHERE product_id = :id");
            $related->execute(['id' => $id]);
            $product[$key] = $related->fetchAll(PDO::FETCH_ASSOC);
        }

        return $product;
    }
}
