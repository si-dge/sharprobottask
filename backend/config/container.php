<?php
declare(strict_types=1);

use App\Domain\ImportService;
use App\Domain\ImageDownloadService;
use App\Domain\KafkaQueueResetter;
use App\Domain\ProductImportService;
use App\Domain\XlsxImportService;
use App\Repository\DatabaseResetRepository;
use App\Repository\ImportRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductImportRepository;
use DI\ContainerBuilder;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

$settings = require __DIR__ . '/settings.php';

return (new ContainerBuilder())->addDefinitions([
    PDO::class => static function () use ($settings): PDO {
        return new PDO(
            $settings['db']['dsn'],
            $settings['db']['user'],
            $settings['db']['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );
    },
    EntityManager::class => static function () use ($settings): EntityManager {
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
    },
    ProductRepository::class => static fn (PDO $pdo): ProductRepository => new ProductRepository($pdo),
    ProductImportRepository::class => static fn (PDO $pdo): ProductImportRepository => new ProductImportRepository($pdo),
    ImportRepository::class => static fn (PDO $pdo): ImportRepository => new ImportRepository($pdo),
    DatabaseResetRepository::class => static fn (PDO $pdo): DatabaseResetRepository => new DatabaseResetRepository($pdo),
    ImportService::class => static fn (ImportRepository $repository): ImportService => new ImportService(
        $repository,
        $settings['kafka'],
        $settings['import_directory'],
        $settings['import_max_file_size'],
    ),
    KafkaQueueResetter::class => static fn (): KafkaQueueResetter => new KafkaQueueResetter(
        $settings['kafka']['brokers'],
        $settings['kafka']['topic'],
    ),
    ImageDownloadService::class => static fn (): ImageDownloadService => new ImageDownloadService(
        $settings['image_directory'],
    ),
    ProductImportService::class => static fn (
        PDO $pdo,
        ProductImportRepository $repository,
        ImageDownloadService $imageDownloader,
    ): ProductImportService => new ProductImportService($pdo, $repository, $imageDownloader),
    XlsxImportService::class => static fn (
        ImportRepository $imports,
        ProductImportService $products,
    ): XlsxImportService => new XlsxImportService($imports, $products),
    \App\Action\ShowImageAction::class => static fn (): \App\Action\ShowImageAction => new \App\Action\ShowImageAction(
        $settings['image_directory'],
    ),
])->build();