<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2017/3/29 0029
 * Time: 22:12
 */

require __DIR__ . '/autoload.php';

use inhere\redis\RedisFactory;

$config = [
    'host' => 'redis',
    'port' => 6379,
    'timeout' => 0.0,
    'database' => 0,
];

$client = RedisFactory::createClient($config);

echo $client->ping(); // +PONG
