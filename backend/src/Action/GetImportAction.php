<?php
declare(strict_types=1);

namespace App\Action;

use App\Repository\ImportRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GetImportAction
{
    public function __construct(private ImportRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $import = $this->repository->find($args['id']);

        return $import
            ? JsonResponse::create($response, $import)
            : JsonResponse::create($response, ['error' => 'Import not found'], 404);
    }
}