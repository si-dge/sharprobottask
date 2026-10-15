<?php

declare(strict_types=1);

namespace App\Domain;

final class ImageDownloadService
{
    public function __construct(private string $directory) {}

    public function download(string $url, string $productId): string
    {
        return $this->downloadMany([$url], $productId)[0];
    }

    /** @param list<string> $urls @return list<string> */
    public function downloadMany(array $urls, string $productId): array
    {
        if ($urls === []) {
            return [];
        }

        if (!is_dir($this->directory) && !mkdir($this->directory, 0777, true) && !is_dir($this->directory)) {
            throw new \RuntimeException('Не удалось создать каталог изображений');
        }

        $multiHandle = curl_multi_init();
        $jobs = [];
        try {
            foreach ($urls as $url) {
                $parts = parse_url($url);
                if (!is_array($parts) || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)) {
                    throw new \RuntimeException('Некорректная ссылка на изображение');
                }

                $extension = strtolower(pathinfo((string) ($parts['path'] ?? ''), PATHINFO_EXTENSION));
                $extension = preg_match('/^[a-z0-9]{1,5}$/', $extension) === 1 ? $extension : 'jpg';
                $filePath = $this->directory . '/' . $productId . '-' . sha1($url) . '.' . $extension;
                $handle = curl_init($url);
                if ($handle === false) {
                    throw new \RuntimeException('Не удалось создать HTTP-запрос: ' . $url);
                }
                curl_setopt_array($handle, [
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 5,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 15,
                    CURLOPT_USERAGENT => 'SharpRobotTask/1.0',
                ]);
                curl_multi_add_handle($multiHandle, $handle);
                $jobs[spl_object_id($handle)] = [
                    'handle' => $handle,
                    'url' => $url,
                    'path' => $filePath,
                ];
            }

            $running = 0;
            do {
                $result = curl_multi_exec($multiHandle, $running);
                if ($result !== CURLM_OK) {
                    throw new \RuntimeException('Ошибка параллельной загрузки изображений');
                }
                if ($running > 0 && curl_multi_select($multiHandle, 1.0) === -1) {
                    usleep(1000);
                }
            } while ($running > 0);

            $paths = [];
            foreach ($jobs as $job) {
                $handle = $job['handle'];
                $contents = curl_multi_getcontent($handle);
                $status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
                if (curl_errno($handle) !== 0 || !is_string($contents) || $contents === '' || $status >= 400) {
                    throw new \RuntimeException('Не удалось скачать изображение: ' . $job['url']);
                }

                if (file_put_contents($job['path'], $contents) === false) {
                    throw new \RuntimeException('Не удалось сохранить изображение: ' . $job['url']);
                }
                $paths[] = $job['path'];
            }

            return $paths;
        } finally {
            foreach ($jobs as $job) {
                curl_multi_remove_handle($multiHandle, $job['handle']);
                curl_close($job['handle']);
            }
            curl_multi_close($multiHandle);
        }
    }
}
