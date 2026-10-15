<?php
declare(strict_types=1);

namespace App\Action;

use App\Repository\ImportRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ListWorkerTasksAction
{
    public function __construct(private ImportRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $query = $request->getQueryParams();
        $limit = isset($query['limit']) ? (int) $query['limit'] : 50;

        return JsonResponse::create($response, [
            'items' => $this->repository->listTasks($limit),
            'total' => $this->repository->countTasks(),
        ]);
    }
}