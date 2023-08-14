<?php
namespace Sai97\LaravelAmqp;

use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

abstract class Queue implements QueueInterface
{
    public function getExchangeType(): string
    {
        return AMQPExchangeType::DIRECT;
    }

    public function getQosPrefetchSize(): int
    {
        return 0;
    }

    public function getQosPrefetchCount(): int
    {
        return 10;
    }

    public function isQosGlobal(): bool
    {
        return false;
    }

    public function getContentType(): string
    {
        return "application/json";
    }

    public function isDeadLetter(): bool
    {
        return false;
    }

    public function isDelay(): bool
    {
        return false;
    }

    public function getExchangeName(): string
    {
        return "";
    }

    public function getRoutingKey(): string
    {
        return "";
    }

    public function getDeadLetterExchangeName(): string
    {
        return "";
    }

    public function getDeadLetterRoutingKey(): string
    {
        return "";
    }

    public function getDeadLetterQueueName(): string
    {
        return "";
    }

    public function getDelayTTL(): int
    {
        return 5000;
    }

    public function getQueueArgs(): array
    {
        return [];
    }

    public function isAutoAck(): bool
    {
        return false;
    }

    public function getDeliveryMode(): int
    {
        return AMQPMessage::DELIVERY_MODE_PERSISTENT;
    }
}
