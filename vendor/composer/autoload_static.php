<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit83f02d069b9f198ed6c45ad18c978de2
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Sai97\\LaravelAmqp\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Sai97\\LaravelAmqp\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Sai97\\LaravelAmqp\\AmqpQueueProviders' => __DIR__ . '/../..' . '/src/AmqpQueueProviders.php',
        'Sai97\\LaravelAmqp\\AmqpQueueServices' => __DIR__ . '/../..' . '/src/AmqpQueueServices.php',
        'Sai97\\LaravelAmqp\\Queue' => __DIR__ . '/../..' . '/src/Queue.php',
        'Sai97\\LaravelAmqp\\QueueFactory' => __DIR__ . '/../..' . '/src/QueueFactory.php',
        'Sai97\\LaravelAmqp\\QueueInterface' => __DIR__ . '/../..' . '/src/QueueInterface.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit83f02d069b9f198ed6c45ad18c978de2::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit83f02d069b9f198ed6c45ad18c978de2::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit83f02d069b9f198ed6c45ad18c978de2::$classMap;

        }, null, ClassLoader::class);
    }
}
