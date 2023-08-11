<?php

use App\Task\RabbitMQ\DefaultQueue;
use App\Task\RabbitMQ\UserQueue;

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
        "user" => UserQueue::class
    ]
];
