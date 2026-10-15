<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_images')]
class ProductImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'images')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\Column(type: 'text')]
    private string $url;

    #[ORM\Column(type: 'text')]
    private string $path = '';

    public function __construct(Product $product, string $url, string $path = '')
    {
        $this->product = $product;
        $this->url = $url;
        $this->path = $path;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
