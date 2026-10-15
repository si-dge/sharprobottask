<?php
declare(strict_types=1);

namespace App\Action;

use App\Domain\ImportService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CreateImportAction
{
    public function __construct(private ImportService $service)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            return JsonResponse::create($response, $this->service->queue($_FILES['file'] ?? null), 202);
        } catch (\InvalidArgumentException $exception) {
            return JsonResponse::create($response, ['error' => $exception->getMessage()], 422);
        } catch (\RuntimeException $exception) {
            return JsonResponse::create($response, ['error' => $exception->getMessage()], 503);
        }
    }
}