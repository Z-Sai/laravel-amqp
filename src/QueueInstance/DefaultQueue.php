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
        return "my_exchange";
    }

    public function getRoutingKey(): string
    {
        return "my_routing_key";
    }

    public function getQueueName(): string
    {
        return "my_queue";
    }

    public function getContentType(): string
    {
        return "text/plain";
    }

    public function isDelay(): bool
    {
        return false;
    }

    public function getDelayTTL(): int
    {
        return 10000;
    }

    public function isDeadLetter(): bool
    {
        return false;
    }

    public function getDeadLetterExchangeName(): string
    {
        return "my_letter_exchange";
    }

    public function getDeadLetterRoutingKey(): string
    {
        return "my_letter_route_key";
    }

    public function getCallback(): callable
    {
        return function (AMQPMessage $msg) {
            $nowDateTime = date("Y-m-d H:i:s", time());
            echo "[{$nowDateTime}] Consumer Received: {$msg->body} {$msg->getDeliveryTag()}\n";
            $msg->ack();
        };
    }
}
