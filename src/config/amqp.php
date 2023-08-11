<?php

namespace Sai97\LaravelAmqp\config;

use Sai97\LaravelAmqp\QueueInstance\DefaultQueue;

return [
    "connection" => [
        "default" => [
            "host" => "192.168.6.102",
            "port" => 5672,
            "user" => "root",
            "password" => "root"
        ]
    ],

    "event" => [
        "default" => DefaultQueue::class,
    ]
];
