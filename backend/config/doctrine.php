<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

return static function (array $settings): EntityManager {
    $ormConfig = ORMSetup::createAttributeMetadataConfiguration(
        $settings['entity_paths'],
        $settings['app_env'] !== 'prod',
    );
    $connection = DriverManager::getConnection([
        'driver' => 'pdo_pgsql',
        'host' => $settings['db']['host'],
        'port' => $settings['db']['port'],
        'dbname' => $settings['db']['name'],
        'user' => $settings['db']['user'],
        'password' => $settings['db']['password'],
    ], $ormConfig);

    return new EntityManager($connection, $ormConfig);
};
