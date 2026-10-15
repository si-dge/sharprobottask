<?php
declare(strict_types=1);

namespace App\Action;

use App\Repository\ProductRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ListProductsAction
{
    public function __construct(private ProductRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return JsonResponse::create($response, $this->repository->paginate($request->getQueryParams()));
    }
}