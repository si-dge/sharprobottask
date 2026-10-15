<?php

declare(strict_types=1);

namespace App\Domain;

final class ImageDownloadService
{
    public function __construct(private string $directory) {}

    public function download(string $url, string $productId): string
    {
        $parts = parse_url($url);
        if (!is_array($parts) || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)) {
            throw new \RuntimeException('Некорректная ссылка на изображение');
        }

        if (!is_dir($this->directory) && !mkdir($this->directory, 0777, true) && !is_dir($this->directory)) {
            throw new \RuntimeException('Не удалось создать каталог изображений');
        }

        $extension = strtolower(pathinfo((string) ($parts['path'] ?? ''), PATHINFO_EXTENSION));
        $extension = preg_match('/^[a-z0-9]{1,5}$/', $extension) === 1 ? $extension : 'jpg';
        $filePath = $this->directory . '/' . $productId . '-' . sha1($url) . '.' . $extension;
        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'follow_location' => 1,
            ],
            'https' => [
                'timeout' => 15,
                'follow_location' => 1,
            ],
        ]);
        $contents = @file_get_contents($url, false, $context);
        if ($contents === false || $contents === '') {
            throw new \RuntimeException('Не удалось скачать изображение: ' . $url);
        }

        if (file_put_contents($filePath, $contents) === false) {
            throw new \RuntimeException('Не удалось сохранить изображение: ' . $url);
        }

        return $filePath;
    }
}
