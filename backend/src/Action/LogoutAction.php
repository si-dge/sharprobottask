<?php
declare(strict_types=1);

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class LogoutAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return JsonResponse::create($response, ['message' => 'Logged out'])
            ->withAddedHeader(
                'Set-Cookie',
                'token=; Path=/; Max-Age=0; HttpOnly; SameSite=Lax',
            );
    }
}
