<?php
declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';
use App\Domain\XlsxImportService;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;

$container = require __DIR__ . '/config/container.php';
$importService = $container->get(XlsxImportService::class);

$config = new Conf();
$config->set('metadata.broker.list', getenv('KAFKA_BROKERS') ?: 'kafka:9092');
$config->set('group.id', 'product-import-workers');
$config->set('enable.auto.commit', 'false');
$config->set('auto.offset.reset', 'earliest');
$config->set('enable.partition.eof', 'false');
$config->set('session.timeout.ms', '10000');

$consumer = new KafkaConsumer($config);
$consumer->subscribe([getenv('KAFKA_TOPIC') ?: 'imports']);

while (true) {
  $message = $consumer->consume(120000);

  if ($message->err !== RD_KAFKA_RESP_ERR_NO_ERROR) {
    continue;
  }

  $job = json_decode($message->payload, true, flags: JSON_THROW_ON_ERROR);
  $importService->import((string) $job['id'], (string) $job['file']);

  $consumer->commit($message);
}
