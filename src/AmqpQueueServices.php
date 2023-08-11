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
    private QueueInterface $queue;

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
     * @param $queue
     * @throws \Exception
     */
    public function __construct($queue)
    {
        $this->queue = $queue;
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
        $connectName = $this->queue->getConnectName();
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
     */
    public function producer(string $body): void
    {
        $channel = $this->getChannel();

        $properties = [
            "content_type" => $this->queue->getContentType(),
            "delivery_mode" => AMQPMessage::DELIVERY_MODE_PERSISTENT //持久化
        ];
        $message = new AMQPMessage($body, $properties);

        $this->initStrategy();

        if ($this->queue->isDelay() && $this->queue->getDelayTTL()) {
            $arguments = [
                "x-delay" => $this->queue->getDelayTTL()
            ];
            $message->set("application_headers", new AMQPTable($arguments));
        }

        //执行发布消息
        $channel->basic_publish($message, $this->queue->getExchangeName(), $this->queue->getRoutingKey() ? $this->queue->getRoutingKey() : $this->queue->getQueueName());
    }

    /**
     * 消费者处理接收并处理消息
     * @return void
     */
    public function consumer(): void
    {
        $channel = $this->getChannel();

        //当前消费者每次只消费一条消息QOS
        $channel->basic_qos(null, $this->queue->getQos(), null);

        $this->initStrategy();

        echo " [*] Waiting for messages. To exit press CTRL+C\n";

        $channel->basic_consume($this->queue->getQueueName(), '', false, false, false, false, $this->queue->getCallback());
        while ($channel->is_open()) {
            $channel->wait();
        }
    }

    //初始化策略
    private function initStrategy()
    {
        $channel = $this->getChannel();

        $argument = [];
        if ($this->queue->getQueueArgs()) {
            $argument = $this->queue->getQueueArgs();
        }

        //开启死信模型
        if ($this->queue->isDeadLetter() && $this->queue->getDeadLetterExchangeName() && $this->queue->getDeadLetterRoutingKey()) {
            //声明业务队列的死信交换机
            $argument = array_merge($argument, [
                "x-dead-letter-exchange" => $this->queue->getDeadLetterExchangeName(), //配置死信交换机
                "x-dead-letter-routing-key" => $this->queue->getDeadLetterRoutingKey(), //配置RoutingKey
            ]);
        }

        //声明队列
        $channel->queue_declare($this->queue->getQueueName(), false, true, false, false, false, new AMQPTable($argument));

        //使用交换机+路由KEY的交互模型
        if ($this->queue->getExchangeName() && $this->queue->getExchangeType() && $this->queue->getRoutingKey()) {
            //声明交换机
            if ($this->queue->isDelay()) {
                $exchangeArgument = [
                    "x-delayed-type" => $this->queue->getExchangeType()
                ];
                $channel->exchange_declare($this->queue->getExchangeName(), "x-delayed-message", false, true, false, false, false, new AMQPTable($exchangeArgument));
            } else {
                $channel->exchange_declare($this->queue->getExchangeName(), $this->queue->getExchangeType(), false, true, false);
            }
            //将队列绑定至交换机
            $channel->queue_bind($this->queue->getQueueName(), $this->queue->getExchangeName(), $this->queue->getRoutingKey());
        }
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
