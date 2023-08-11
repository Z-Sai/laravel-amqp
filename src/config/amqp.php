<?php

use App\QueueInstance\DefaultQueue;

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
        "default" => DefaultQueue::class,
    ]
];
