<?php
declare(strict_types=1);

namespace App\Domain;

use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\TopicPartition;

final class KafkaQueueResetter
{
    public function __construct(
        private string $brokers,
        private string $topicName,
    ) {
    }

    public function reset(): void
    {
        $lastException = null;
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $consumer = null;
            try {
                $configuration = new Conf();
                $configuration->set('metadata.broker.list', $this->brokers);
                $configuration->set('group.id', 'product-import-workers');
                $configuration->set('enable.auto.commit', 'false');

                $consumer = new KafkaConsumer($configuration);
                $consumer->subscribe([$this->topicName]);
                $consumer->consume(1000);

                if ($consumer->getAssignment() === []) {
                    $consumer->close();

                    return;
                }

                $highWatermark = 0;
                $lowWatermark = 0;
                $consumer->queryWatermarkOffsets(
                    $this->topicName,
                    0,
                    $lowWatermark,
                    $highWatermark,
                    10000,
                );

                $offset = new TopicPartition($this->topicName, 0, $highWatermark);
                $consumer->commit([$offset]);
                $consumer->close();

                return;
            } catch (\Throwable $exception) {
                $lastException = $exception;
                if ($consumer !== null) {
                    $consumer->close();
                }
                usleep(250000);
            }
        }

        if ($lastException instanceof \RdKafka\Exception && preg_match(
            '/group generation|unknown member/i',
            $lastException->getMessage(),
        ) === 1) {
            return;
        }

        throw $lastException ?? new \RuntimeException('Unable to reset Kafka queue');
    }
}