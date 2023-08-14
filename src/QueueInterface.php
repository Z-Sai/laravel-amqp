<?php
namespace Sai97\LaravelAmqp;

interface QueueInterface
{
    //获取连接名称
    public function getConnectName(): string;

    //获取交换机名称
    public function getExchangeName(): string;

    //获取交换机类型
    public function getExchangeType(): string;

    //获取队列名称
    public function getQueueName(): string;

    //获取路由KEY
    public function getRoutingKey(): string;

    //获取QOS.PrefetchSize
    public function getQosPrefetchSize(): int;

    //获取QOS.PrefetchCount
    public function getQosPrefetchCount(): int;

    //是否开启QOS.Global
    public function isQosGlobal(): bool;

    //获取ContentType
    public function getContentType(): string;

    //是否开启死信模式
    public function isDeadLetter(): bool;

    //获取死信交换机名称
    public function getDeadLetterExchangeName(): string;

    //获取死信路由KEY
    public function getDeadLetterRoutingKey(): string;

    //获取死信队列名称
    public function getDeadLetterQueueName(): string;

    //是否开启延迟任务
    public function isDelay(): bool;

    //获取延迟任务过期时长
    public function getDelayTTL(): int;

    //获取队列附加参数
    public function getQueueArgs(): array;

    //获取回调函数
    public function getCallback(): callable;

    //是否自动提交ACK
    public function isAutoAck(): bool;

    //获取message.delivery_mode类型
    public function getMessageDeliveryMode(): int;

    public function getConsumerTag(): string;
    public function isConsumerNoLocal(): bool;
    public function isConsumerExclusive(): bool;
    public function isConsumerNowait(): bool;
    public function getConsumerTicket(): int;
    public function getConsumerArgs(): array;
    public function isQueuePassive(): bool;
    public function isQueueDurable(): bool;
    public function isQueueExclusive(): bool;
    public function isQueueAutoDelete(): bool;
    public function isQueueNowait(): bool;
    public function getQueueTicket(): int;
    public function isExchangePassive(): bool;
    public function isExchangeDurable(): bool;
    public function isExchangeAutoDelete(): bool;
    public function isExchangeInternal(): bool;
    public function isExchangeNowait(): bool;
    public function getExchangeTicket(): bool;
    public function getExchangeArgs(): array;
}
