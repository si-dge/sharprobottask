<?php
declare(strict_types=1);

namespace App\Domain;

use App\Repository\ProductImportRepository;
use PDO;

final class ProductImportService
{
    public function __construct(
        private PDO $pdo,
        private ProductImportRepository $repository,
        private ImageDownloadService $imageDownloader,
    ) {
    }

    public function importRow(array $data): void
    {
        $code = trim((string) ($data['Внешний код'] ?? ''));
        $name = trim((string) ($data['Наименование'] ?? ''));
        if ($code === '' || $name === '') {
            throw new \RuntimeException('Внешний код и Наименование обязательны');
        }

        $uuid = trim((string) ($data['UUID'] ?? ''));
        if ($uuid !== '' && preg_match(
                '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $uuid,
        ) !== 1) {
            throw new \RuntimeException('UUID имеет некорректный формат');
        }
        $uuid = $uuid !== '' ? $uuid : $this->uuid();

        $price = $this->number($data['Цена: Цена продажи'] ?? 0);
        $purchase = $this->number($data['Закупочная цена'] ?? $data['Цена: Закупочная цена'] ?? 0);
        $discount = $price > 0 ? round(($price - $purchase) / $price * 100, 2) : 0;
        $images = $this->downloadImages($data, $uuid);

        $this->pdo->beginTransaction();
        try {
            $productId = $this->repository->upsertProduct([
                'id' => $uuid,
                'external_code' => $code,
                'name' => $name,
                'description' => (string) ($data['Описание'] ?? ''),
                'price' => $price,
                'discount' => $discount,
            ]);
            $this->repository->deleteAttributes($productId);
            $this->repository->deleteImages($productId);

            foreach ($data as $key => $value) {
                if (!str_starts_with($key, 'Доп. поле') || $value === '' || $this->isImageColumn($key)) {
                    continue;
                }
                $this->repository->addAttribute($productId, $key, (string) $value);
            }
            foreach ($images as $image) {
                $this->repository->addImage($productId, $image['url'], $image['path']);
            }
            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function downloadImages(array $data, string $productId): array
    {
        $images = [];
        foreach ($data as $key => $value) {
            if (!$this->isImageColumn($key) || trim((string) $value) === '') {
                continue;
            }
            foreach (preg_split('/[,;\r\n]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $url) {
                $url = trim($url);
                $images[] = [
                    'url' => $url,
                    'path' => $this->imageDownloader->download($url, $productId),
                ];
            }
        }
        return $images;
    }

    private function isImageColumn(string $key): bool
    {
        return str_contains($key, 'Ссылки на фото');
    }

    private function number(mixed $value): float
    {
        return (float) str_replace(',', '.', (string) $value);
    }

    private function uuid(): string
    {
        if (function_exists('uuid_create')) {
            return (string) uuid_create(UUID_TYPE_RANDOM);
        }

        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }
}