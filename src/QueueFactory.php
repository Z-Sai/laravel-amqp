<?php
namespace Sai\LaravelAmqp;

class QueueFactory
{
    /**
     * @throws \Exception
     */
    public static function getInstance(string $class): QueueInterface
    {
        if (empty($class)) throw new \Exception("QueueFactory getInstance func the class params empty!");
        return new $class;
    }
}
