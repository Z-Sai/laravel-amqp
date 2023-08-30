<?php

namespace Sai97\LaravelAmqp;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use PhpAmqpLib\Channel\AMQPChannel;

class AmqpQueueServices
{
    /**
     * 当前队列实例
     * @var QueueInterface
     */
    private $queueJob;

    /**
     * 当前通道
     * @var AMQPChannel $channel
     */
    private $channel;

    /**
     * 当前连接
     * @var AMQPStreamConnection $connection
     */
    private $connection;

    /**
     * 构造函数
     * @param QueueInterface $queueJob
     * @throws \Exception
     */
    public function __construct(QueueInterface $queueJob)
    {
        $this->queueJob = $queueJob;
        $this->connection($this->getConfigs());
    }

    /**
     * 获取通道
     * @return AMQPChannel
     */
    public function getChannel(): AMQPChannel
    {
        if ($this->channel instanceof AMQPChannel) {
            return $this->channel;
        }
        $this->channel = $this->connection->channel();
        return $this->channel;
    }

    /**
     * 获取配置
     * @throws \Exception
     */
    protected function getConfigs(): array
    {
        $connectName = $this->queueJob->getConnectName();
        $amqpConfig = config("amqp");
        if (!isset($amqpConfig["connection"][$connectName]) || empty($connectionConfig = $amqpConfig["connection"][$connectName])) {
            throw new \Exception("rabbitmq config with connection is not found!");
        }
        return $connectionConfig;
    }

    /**
     * 获取连接
     * @throws \Exception
     */
    protected function connection(array $config): void
    {
        if (!($this->connection instanceof AMQPStreamConnection)) {
            $this->connection = new AMQPStreamConnection($config["host"], $config["port"], $config["user"], $config["password"]);
        }
    }


    /**
     * 生产者发送消息
     * @return void
     * @throws \Exception
     */
    public function producer(string $body): void
    {
        $channel = $this->getChannel();

        $properties = [
            "content_type" => $this->queueJob->getContentType(),
            "delivery_mode" => $this->queueJob->getMessageDeliveryMode()
        ];
        $message = new AMQPMessage($body, $properties);

        //初始化策略
        $this->initStrategy("producer");

        if ($this->queueJob->isDelay() && $this->queueJob->getDelayTTL()) {
            $arguments = [
                "x-delay" => $this->queueJob->getDelayTTL()
            ];
            $message->set("application_headers", new AMQPTable($arguments));
        }

        //执行发布消息
        $channel->basic_publish(
            $message,
            $this->queueJob->getExchangeName(),
            $this->queueJob->getRoutingKey() ? $this->queueJob->getRoutingKey() : $this->queueJob->getQueueName()
        );
    }

    /**
     * 消费者处理接收并处理消息
     * @return void
     * @throws \Exception
     */
    public function consumer(): void
    {
        $channel = $this->getChannel();

        //当前消费者QOS相关配置
        $channel->basic_qos($this->queueJob->getQosPrefetchSize(), $this->queueJob->getQosPrefetchCount(), $this->queueJob->isQosGlobal());

        //初始化策略
        $this->initStrategy("consumer");

        $datetime = date("Y-m-d H:i:s", time());
        echo " [{$datetime}] ChannelId:{$channel->getChannelId()} Waiting for messages:\n";

        $channel->basic_consume(
            $this->queueJob->getQueueName(),
            $this->queueJob->getConsumerTag(),
            $this->queueJob->isConsumerNoLocal(),
            $this->queueJob->isAutoAck(),
            $this->queueJob->isConsumerExclusive(),
            $this->queueJob->isConsumerNowait(),
            $this->queueJob->getCallback(),
            ($this->queueJob->getConsumerTicket() > 0) ? $this->queueJob->getConsumerTicket() : null,
            new AMQPTable($this->queueJob->getConsumerArgs())
        );

        while ($channel->is_open()) {
            $channel->wait();
        }
    }

    /**
     * 初始化策略
     * @throws \Exception
     */
    private function initStrategy(string $caller)
    {
        if (!in_array($caller, ["producer", "consumer"])) throw new \Exception("initStrategy scene Params is Fail.");

        $channel = $this->getChannel();

        if ($this->queueJob->getExchangeName() && $this->queueJob->getExchangeType()) { //使用交换机交互模型

            $this->handlerExchangeDeclare();

            //如果是消费者调用方
            if ($caller == "consumer") {

                $queueName = $this->handlerQueueDeclare();

                //获取队列绑定交换机的路由KEY,优先选择getQueueBindRoutingKey
                $routingKey = $this->queueJob->getQueueBindRoutingKey() ? $this->queueJob->getQueueBindRoutingKey() : $this->queueJob->getRoutingKey();

                //将队列绑定至交换机
                $channel->queue_bind($queueName, $this->queueJob->getExchangeName(), $routingKey);
            }
        } else { //不使用交换机交互模型
            $this->handlerQueueDeclare();
        }
    }

    /**
     * 处理声明交换机
     * @return void
     */
    private function handlerExchangeDeclare(): void
    {
        $channel = $this->getChannel();

        $exchangeType = $this->queueJob->getExchangeType();

        //交换机附加参数
        $exchangeArgument = $this->queueJob->getExchangeArgs();

        //延迟队列
        if ($this->queueJob->isDelay()) {
            $exchangeArgument = array_merge($exchangeArgument, [
                "x-delayed-type" => $exchangeType
            ]);
            $exchangeType = "x-delayed-message";
        }

        //初始化交换机
        $channel->exchange_declare(
            $this->queueJob->getExchangeName(),
            $exchangeType,
            $this->queueJob->isExchangePassive(),
            $this->queueJob->isExchangeDurable(),
            $this->queueJob->isExchangeAutoDelete(),
            $this->queueJob->isExchangeInternal(),
            $this->queueJob->isExchangeNowait(),
            new AMQPTable($exchangeArgument),
            ($this->queueJob->getExchangeTicket() > 0) ? $this->queueJob->getExchangeTicket() : null
        );
    }

    /**
     * 处理声明队列
     * @return string 队列名称
     */
    private function handlerQueueDeclare(): string
    {
        $channel = $this->getChannel();

        //queue附加参数
        $argument = $this->queueJob->getQueueArgs();

        //开启死信队列模式
        if ($this->queueJob->isDeadLetter() && $this->queueJob->getDeadLetterExchangeName() && $this->queueJob->getDeadLetterRoutingKey()) {
            //声明业务队列的死信交换机
            $argument = array_merge($argument, [
                "x-dead-letter-exchange" => $this->queueJob->getDeadLetterExchangeName(), //配置死信交换机
                "x-dead-letter-routing-key" => $this->queueJob->getDeadLetterRoutingKey(), //配置RoutingKey
            ]);
        }

        //声明队列
        list($queueName, ,) = $channel->queue_declare(
            $this->queueJob->getQueueName(),
            $this->queueJob->isQueuePassive(),
            $this->queueJob->isQueueDurable(),
            $this->queueJob->isQueueExclusive(),
            $this->queueJob->isQueueAutoDelete(),
            $this->queueJob->isQueueNowait(),
            new AMQPTable($argument),
            ($this->queueJob->getQueueTicket() > 0) ? $this->queueJob->getQueueTicket() : null
        );

        return $queueName;
    }

    /**
     * 析构函数，释放相关服务连接
     * @throws \Exception
     */
    public function __destruct()
    {
        if ($this->channel instanceof AMQPChannel) {
            $this->channel->close();
        }
        if ($this->connection instanceof AMQPStreamConnection) {
            $this->connection->close();
        }
    }
}
