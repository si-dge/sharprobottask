<?php

declare(strict_types=1);

namespace Fixtures;

use App\Entity\Product;
use App\Entity\ProductAttribute;
use App\Entity\ProductImage;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class ProductsFixture extends AbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        $products = [
            [
                'id' => '11111111-1111-4111-8111-111111111111',
                'external_code' => 'DEMO-001',
                'name' => 'Полевой рюкзак 24 л',
                'description' => 'Водостойкий рюкзак для коротких поездок и прогулок.',
                'price' => '7490.00',
                'discount' => '25.35',
                'attributes' => [
                    ['key' => 'Цвет', 'value' => 'Графит'],
                    ['key' => 'Объем', 'value' => '24 л'],
                ],
                'images' => [
                    [
                        'url' => 'https://images.example.test/products/demo-001.jpg',
                        'path' => 'fixtures/demo-001.jpg',
                    ],
                ],
            ],
            [
                'id' => '22222222-2222-4222-8222-222222222222',
                'external_code' => 'DEMO-002',
                'name' => 'Термокружка 450 мл',
                'description' => 'Стальная термокружка с герметичной крышкой.',
                'price' => '2190.00',
                'discount' => '18.72',
                'attributes' => [
                    ['key' => 'Материал', 'value' => 'Нержавеющая сталь'],
                ],
                'images' => [
                    [
                        'url' => 'https://images.example.test/products/demo-002.jpg',
                        'path' => 'fixtures/demo-002.jpg',
                    ],
                ],
            ],
        ];

        foreach ($products as $data) {
            $product = new Product($data['id'], $data['external_code'], $data['name']);
            $product->setDescription($data['description']);
            $product->setPrice($data['price']);
            $product->setDiscount($data['discount']);
            $manager->persist($product);

            foreach ($data['attributes'] as $attribute) {
                $manager->persist(new ProductAttribute($product, $attribute['key'], $attribute['value']));
            }

            foreach ($data['images'] as $image) {
                $manager->persist(new ProductImage($product, $image['url'], $image['path']));
            }
        }

        $manager->flush();
    }
}