<?php
declare(strict_types=1);

namespace App\Action;

use Psr\Http\Message\ResponseInterface;

final class JsonResponse
{
    public static function create(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write((string) json_encode($data, JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}