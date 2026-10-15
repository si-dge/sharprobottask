<?php
declare(strict_types=1);

use Dotenv\Dotenv;

$projectDirectory = dirname(__DIR__);
if (is_file($projectDirectory . '/.env')) {
    Dotenv::createImmutable($projectDirectory)->safeLoad();
}

$env = static function (string $name, mixed $default = null): mixed {
    $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);

    return $value === false || $value === null || $value === '' ? $default : $value;
};

$dbDsn = (string) $env('DB_DSN', 'pgsql:host=localhost;port=5432;dbname=products');
preg_match('/host=([^;]+)/', $dbDsn, $hostMatch);
preg_match('/port=([^;]+)/', $dbDsn, $portMatch);
preg_match('/dbname=([^;]+)/', $dbDsn, $nameMatch);

return [
    'app_env' => (string) $env('APP_ENV', 'dev'),
    'auth' => [
        'jwt_secret' => (string) $env('JWT_SECRET', 'change-this-local-jwt-secret'),
        'jwt_issuer' => (string) $env('JWT_ISSUER', 'sharp-robot-task'),
        'username' => (string) $env('AUTH_USERNAME', 'admin'),
        'password' => (string) $env('AUTH_PASSWORD', 'admin'),
    ],
    'db' => [
        'dsn' => $dbDsn,
        'user' => (string) $env('DB_USER', 'products'),
        'password' => (string) $env('DB_PASSWORD', 'products'),
        'host' => (string) $env('DB_HOST', $hostMatch[1] ?? 'localhost'),
        'port' => (int) $env('DB_PORT', $portMatch[1] ?? 5432),
        'name' => (string) $env('DB_NAME', $nameMatch[1] ?? 'products'),
    ],
    'kafka' => [
        'brokers' => (string) $env('KAFKA_BROKERS', 'kafka:9092'),
        'topic' => (string) $env('KAFKA_TOPIC', 'imports'),
    ],
    'import_directory' => sys_get_temp_dir() . '/imports',
    'image_directory' => sys_get_temp_dir() . '/imports/images',
    'import_max_file_size' => (int) $env('IMPORT_MAX_SIZE', 50 * 1024 * 1024),
    'entity_paths' => [dirname(__DIR__) . '/src/Entity'],
];