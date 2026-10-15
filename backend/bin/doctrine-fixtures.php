#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Fixtures\ProductsFixture;

$settings = require dirname(__DIR__) . '/config/settings.php';
$createEntityManager = require dirname(__DIR__) . '/config/doctrine.php';
$entityManager = $createEntityManager($settings);

$loader = new Loader();
$loader->addFixture(new ProductsFixture());

$executor = new ORMExecutor($entityManager, new ORMPurger($entityManager));
$executor->execute($loader->getFixtures());

fwrite(STDOUT, "Doctrine fixtures loaded.\n");
