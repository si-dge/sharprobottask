#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use App\Entity\Product;
use App\Entity\ProductAttribute;
use App\Entity\ProductImage;

$container = require dirname(__DIR__) . '/config/container.php';
/** @var EntityManager $entityManager */
$entityManager = $container->get(EntityManager::class);
$metadata = [
    $entityManager->getClassMetadata(Product::class),
    $entityManager->getClassMetadata(ProductAttribute::class),
    $entityManager->getClassMetadata(ProductImage::class),
];
(new SchemaTool($entityManager))->updateSchema($metadata);
fwrite(STDOUT, "Doctrine schema is up to date.\n");
