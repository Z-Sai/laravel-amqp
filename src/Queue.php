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
        return "text/plain";
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

    public function getQueueBindRoutingKey(): string {
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

    public function getMessageDeliveryMode(): int
    {
        return AMQPMessage::DELIVERY_MODE_PERSISTENT;
    }

    public function getConsumerTag(): string
    {
        return "";
    }

    public function isConsumerNoLocal(): bool
    {
        return false;
    }

    public function isConsumerExclusive(): bool
    {
        return false;
    }

    public function isConsumerNowait(): bool
    {
        return false;
    }

    public function getConsumerTicket(): int
    {
        return 0;
    }

    public function getConsumerArgs(): array
    {
        return [];
    }

    public function isQueuePassive(): bool
    {
        return false;
    }

    public function isQueueDurable(): bool
    {
        return true;
    }

    public function isQueueExclusive(): bool
    {
        return false;
    }

    public function isQueueAutoDelete(): bool
    {
        return false;
    }

    public function isQueueNowait(): bool
    {
        return false;
    }

    public function getQueueTicket(): int
    {
        return 0;
    }

    public function isExchangePassive(): bool
    {
        return false;
    }

    public function isExchangeDurable(): bool
    {
        return true;
    }

    public function isExchangeAutoDelete(): bool
    {
        return false;
    }

    public function isExchangeInternal(): bool
    {
        return false;
    }

    public function isExchangeNowait(): bool
    {
        return false;
    }

    public function getExchangeTicket(): bool
    {
        return 0;
    }

    public function getExchangeArgs(): array
    {
        return [];
    }
}
