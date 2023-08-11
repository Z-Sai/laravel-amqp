<?php
namespace Sai\LaravelAmqp;

use PhpAmqpLib\Exchange\AMQPExchangeType;

abstract class Queue implements QueueInterface
{
    public function getExchangeType(): string
    {
        return AMQPExchangeType::DIRECT;
    }

    public function getQos(): int
    {
        return 1;
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
}
