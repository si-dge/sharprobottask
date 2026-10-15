<?php

declare(strict_types=1);

namespace App\Action;

use App\Domain\KafkaQueueResetter;
use App\Repository\DatabaseResetRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ResetDatabaseAction
{
    public function __construct(
        private DatabaseResetRepository $repository,
        private KafkaQueueResetter $queueResetter,
    ) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $this->queueResetter->reset();
            $this->repository->truncate();
        } catch (\Throwable $exception) {
            return JsonResponse::create($response, [
                'error' => 'Unable to reset Kafka queue and database',
            ], 503);
        }

        return JsonResponse::create($response, [
            'status' => 'ok',
            'message' => 'Kafka queue and database data have been cleared',
        ]);
    }
}
