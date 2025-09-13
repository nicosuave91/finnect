<?php

namespace App\Services;

use RdKafka\Conf;
use RdKafka\Producer;
use Illuminate\Support\Facades\Log;

class KafkaService
{
    private Producer $producer;

    public function __construct()
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', env('KAFKA_BROKERS', 'kafka:9092'));
        $this->producer = new Producer($conf);
    }

    public function publish(string $topic, array $message): void
    {
        $topicProducer = $this->producer->newTopic($topic);
        $payload = json_encode($message);
        $topicProducer->produce(RD_KAFKA_PARTITION_UA, 0, $payload);
        $this->producer->poll(0);
        $result = $this->producer->flush(10000);
        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
            Log::error('Kafka flush error', ['code' => $result]);
        }
    }
}
