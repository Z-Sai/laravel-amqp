<?php
namespace Sai97\LaravelAmqp;

use Illuminate\Support\ServiceProvider;

class AmqpQueueProviders extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // 发布配置文件
        $this->publishes([
            __DIR__.'/config/amqp.php' => config_path('amqp.php'),
        ]);

        $this->publishes([
            __DIR__ . '/QueueJob' => app_path("QueueJob"),
        ]);
    }
}
