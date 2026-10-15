<?php

declare(strict_types=1);

use App\Action\CreateImportAction;
use App\Action\GetImportAction;
use App\Action\HealthAction;
use App\Action\ShowProductAction;
use App\Action\ListProductsAction;
use App\Action\ListWorkerTasksAction;
use App\Action\LoginAction;
use App\Action\LogoutAction;
use App\Action\ResetDatabaseAction;
use App\Action\ShowImageAction;
use Slim\App;

/** @var App $app */
$app->get('/api/health', HealthAction::class);
$app->post('/api/login', LoginAction::class);
$app->post('/api/logout', LogoutAction::class);
$app->get('/api/products', ListProductsAction::class);
$app->get('/api/products/{id}', ShowProductAction::class);
$app->get('/api/worker/tasks', ListWorkerTasksAction::class);
$app->get('/api/images/{filename:.+}', ShowImageAction::class);
$app->post('/api/database/reset', ResetDatabaseAction::class);
$app->post('/api/imports', CreateImportAction::class);
$app->get('/api/imports/{id}', GetImportAction::class);
