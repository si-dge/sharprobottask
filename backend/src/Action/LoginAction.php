<?php

declare(strict_types=1);

namespace App\Action;

use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class LoginAction
{
    /** @param array{jwt_secret: string, jwt_issuer: string, username: string, password: string} $auth */
    public function __construct(private array $auth) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $request->getParsedBody();
        $credentials = is_array($body) ? $body : [];
        $username = is_string($credentials['username'] ?? null) ? $credentials['username'] : '';
        $password = is_string($credentials['password'] ?? null) ? $credentials['password'] : '';

        if (!$this->isValidCredentials($username, $password)) {
            return JsonResponse::create($response, ['error' => 'Invalid credentials'], 401);
        }

        $now = time();
        $token = JWT::encode([
            'iss' => $this->auth['jwt_issuer'],
            'sub' => $username,
            'iat' => $now,
            'exp' => $now + 3600,
        ], $this->auth['jwt_secret'], 'HS256');

        return JsonResponse::create($response, [
            'token' => $token,
            'tokenType' => 'Bearer',
            'expiresIn' => 3600,
            'user' => ['username' => $username],
        ])->withAddedHeader(
            'Set-Cookie',
            sprintf('token=%s; Path=/; Max-Age=3600; HttpOnly; SameSite=Lax', $token),
        );
    }

    private function isValidCredentials(string $username, string $password): bool
    {
        return hash_equals($this->auth['username'], $username)
            && hash_equals($this->auth['password'], $password);
    }
}
