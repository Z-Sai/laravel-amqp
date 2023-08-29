# laravel-amqp
基于php-amqplib/php-amqplib组件适配laravel框架的amqp封装库


支持便捷可配置的队列工作模式 [官网详情](https://www.rabbitmq.com/getstarted.html)

在此基础上可支持延迟消息、死信队列等机制。

### 环境要求：
PHP版本: ^7.3|^8.0

需要开启的扩展: socket

其他: 
1. 如果需要实现延迟任务需要安装对应版本的rabbitmq[延迟插件](https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases)，以rabbitmq3.9.0版本为例:
```
wget https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases/download/3.9.0/rabbitmq_delayed_message_exchange-3.9.0.ez
cp rabbitmq_delayed_message_exchange-3.9.0.ez /opt/rabbitmq/plugins/
rabbitmq-plugins enable rabbitmq_delayed_message_exchange
```

### 用法：

#### 第一步 安装组件：
```
composer require sai97/laravel-amqp
```

#### 第二步 发布服务以及配置：
```
php artisan vendor:publish --provider="Sai97\LaravelAmqp\AmqpQueueProviders"
```
执行完后会分别在app/config目录下生成amqp.php配置文件

amqp.php

```php
<?php

use App\QueueJob\DefaultQueueJob;

return [
    "connection" => [
        "default" => [
            "host" => env("AMQP_HOST", "127.0.0.1"),
            "port" => env("AMQP_PORT", 5672),
            "user" => env("AMQP_USER", "root"),
            "password" => env("AMQP_PASSWORD", "root")
        ]
    ],

    "event" => [
        "default" => DefaultQueueJob::class,
    ]
];
```

DefaultQueueJob.php

```php
<?php

namespace App\QueueJob;

use PhpAmqpLib\Message\AMQPMessage;
use Sai97\LaravelAmqp\Queue;

class DefaultQueueJob extends Queue
{
    public function getConnectName(): string
    {
        return "default";
    }

    public function getQueueName(): string
    {
        return "myQueue";
    }

    public function getCallback(): callable
    {
        return function (AMQPMessage $message){
            $nowDate = date("Y-m-d H:i:s", time());
            echo "[{$nowDate}] The consumer message: {$message->body}" . PHP_EOL;
            $message->ack();
        };
    }
}

```

connection为amqp连接配置，可根据自身业务去调整，完全对应php-amqplib/php-amqplib相关配置项，
event是队列实例标识，最好和connection用相同的key以便管理。

#### 目前可支持相关接口项：
```php
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
```

当然你也可以自定义队列实例，只要继承Sai97\LaravelAmqp\Queue基类即可，具体功能配置参数参考Sai97\LaravelAmqp\QueueInterface。

### 代码示例:

#### 生产者:
```php
$message = "This is message...";
$amqpQueueServices = new AmqpQueueServices(new DefaultQueueJob);
$amqpQueueServices->producer($message);
```

#### 消费者:
利用laravel自带的Command去定义一个RabbitMQWorker自定义命令行，仅需要定义一次，后续只需要更改amqp.php配置文件添加不同的队列实例绑定关系即可，以下是RabbitMQWorker演示代码：
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Sai97\LaravelAmqp\AmqpQueueServices;

class RabbitMQWorker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:worker {event}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'rabbitmq worker 消费进程';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {

            $event = $this->argument("event");

            $eventConfig = config("amqp.event");
            if (!isset($eventConfig[$event]) || empty($entity = $eventConfig[$event])) {
                return $this->error("未知的事件: {$event}");
            }
            
            $this->info("rabbitmq worker of event[{$event}] process start ...");

            $amqpQueueServices = new AmqpQueueServices(new $entity);
            $amqpQueueServices->consumer();

        } catch (\Throwable $throwable) {
            $event = $event ?? "";
            $this->error($throwable->getFile() . " [{$throwable->getLine()}]");
            return $this->error("rabbitmq worker of event[{$event}] process error:{$throwable->getMessage()}");
        }

        $this->info("rabbitmq worker of event[{$event}] process stop ...");
    }
}

```
完成RabbitMQWorker消费者命令后，我们只需执行php artisan rabbitmq:worker default 完成监听，其中default是可变的，请根据的amqp.php配置中的队列实例绑定标识去输入。

因为队列的消费者都需要是守护进程，所以我们可以依托supervisord进程管理器去定义RabbitMQWorker消费者命令，这样可以保证进程可后台允许以及重启启动等，以下是supervisord.conf配置文件示例：
```
[program:rabbitmq-worker-default]
#process_name=%(program_name)s_%(process_num)d
process_name=worker_%(process_num)d
numprocs=2
command=/usr/local/bin/php /app/www/laravel8/artisan rabbitmq:worker default
autostart=true
autorestart=true
startretries=3
priority=3
stdout_logfile=/var/log/rabbitmq-worker-default.log
redirect_stderr=true
```
搭配supervisord来进行管理消费者进程有许多便捷的方面：
1. 如果需要新增一个队列实例，只需要按照上述格式复制一个program，可以在不影响其他进程的情况下进程更新supervisord配置：
```
supervisorctl -c /etc/supervisord/supervisord.conf update
```

2. 通过配置numprocs参数来设定需要开启多少个相同配置项的消费者worker，这在任务分发、并行处理等场景十分适用，大大提高消费者执行效率。

这里不详细叙述supervisord相关操作，具体可查看[supervisord官方文档](http://supervisord.org/introduction.html)。
