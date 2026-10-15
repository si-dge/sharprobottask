<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(name: 'external_code', type: 'string', length: 255, unique: true)]
    private string $externalCode;

    #[ORM\Column(type: 'string', length: 500)]
    private string $name;

    #[ORM\Column(type: 'text')]
    private string $description = '';

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2)]
    private string $price = '0.00';

    #[ORM\Column(type: 'decimal', precision: 7, scale: 2)]
    private string $discount = '0.00';

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, ProductImage> */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductImage::class)]
    private Collection $images;

    public function __construct(string $id, string $externalCode, string $name)
    {
        $this->id = $id;
        $this->externalCode = $externalCode;
        $this->name = $name;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->images = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setPrice(string $price): void
    {
        $this->price = $price;
    }

    public function setDiscount(string $discount): void
    {
        $this->discount = $discount;
    }
}
