<?php
declare(strict_types=1);

namespace App\Action;

use App\Repository\ProductRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ShowProductAction
{
    public function __construct(private ProductRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $product = $this->repository->findWithRelations($args['id']);

        if ($product !== null) {
            foreach ($product['images'] as &$image) {
                $image['path'] = '/api/images/' . rawurlencode(basename((string) $image['path']));
            }
            unset($image);
        }

        return $product
            ? JsonResponse::create($response, $product)
            : JsonResponse::create($response, ['error' => 'Product not found'], 404);
    }
}