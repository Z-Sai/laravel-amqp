<?php

namespace App\QueueInstance;

use PhpAmqpLib\Message\AMQPMessage;
use Sai97\LaravelAmqp\Queue;

class DefaultQueue extends Queue
{
    public function getConnectName(): string
    {
        return "default";
    }

    public function getExchangeName(): string
    {
        return "delayed_exchange";
    }

    public function getRoutingKey(): string
    {
        return "delayed_queue_routing_key";
    }

    public function getQueueName(): string
    {
        return "delayed_queue";
    }

    public function getContentType(): string
    {
        return "text/plain";
    }

    public function isDelay(): bool
    {
        return true;
    }

    public function getDelayTTL(): int
    {
        return 10000;
    }

    public function isDeadLetter(): bool
    {
        return true;
    }

    public function getDeadLetterExchangeName(): string
    {
        return "dead_letter_exchange";
    }

    public function getDeadLetterRoutingKey(): string
    {
        return "dead_letter_route_key";
    }

    public function getCallback(): callable
    {
        return function (AMQPMessage $msg) {
            echo "[default] Received {$msg->body}\n";
            $msg->ack();
        };
    }
}
