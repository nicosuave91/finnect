<?php

namespace App\Consumers;

use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\TopicPartition;
use Illuminate\Support\Facades\Log;

class LoanStateChangeConsumer
{
    private KafkaConsumer $consumer;

    public function __construct()
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', env('KAFKA_BROKERS', 'kafka:9092'));
        $conf->set('group.id', 'loan_state_change_consumer');
        $conf->set('auto.offset.reset', 'earliest');
        $this->consumer = new KafkaConsumer($conf);
        $this->consumer->subscribe(['loan_state_changes']);
    }

    public function consume(callable $handler): void
    {
        $message = $this->consumer->consume(1000);
        if ($message === null || $message->err) {
            return;
        }

        $payload = json_decode($message->payload, true);
        try {
            $handler($payload);
        } catch (\Throwable $e) {
            Log::error('Loan state change consumer error: '.$e->getMessage());
        }
    }
}
