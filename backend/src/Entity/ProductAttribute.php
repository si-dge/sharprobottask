<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_attributes')]
class ProductAttribute
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\Column(name: 'key', type: 'string', length: 255)]
    private string $key;

    #[ORM\Column(type: 'text')]
    private string $value;

    public function __construct(Product $product, string $key, string $value)
    {
        $this->product = $product;
        $this->key = $key;
        $this->value = $value;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
