<?php

declare(strict_types=1);

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ShowImageAction
{
    public function __construct(private string $directory) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $filename = (string) ($args['filename'] ?? '');
        if ($filename === '' || $filename !== basename($filename)) {
            return JsonResponse::create($response, ['error' => 'Image not found'], 404);
        }

        $path = $this->directory . '/' . $filename;
        if (!is_file($path) || !is_readable($path)) {
            return JsonResponse::create($response, ['error' => 'Image not found'], 404);
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!is_string($mimeType) || !str_starts_with($mimeType, 'image/')) {
            return JsonResponse::create($response, ['error' => 'Image not found'], 404);
        }

        $stream = fopen($path, 'rb');
        if ($stream === false) {
            return JsonResponse::create($response, ['error' => 'Image not found'], 404);
        }

        $response->getBody()->write(stream_get_contents($stream) ?: '');
        fclose($stream);

        return $response
            ->withHeader('Content-Type', $mimeType)
            ->withHeader('Content-Length', (string) filesize($path))
            ->withHeader('Cache-Control', 'public, max-age=86400');
    }
}
