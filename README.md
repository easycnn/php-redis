# php-redis

> a simple redis library of the php



## Install

- use composer

edit `composer.json`

_require_ add

```
"inhere/php-redis": "dev-master",
```

_repositories_ add 

```
"repositories": [
    {
      "type": "git",
      "url": "https://github.com/inhere/php-redis"
    }
  ]
```

run: `composer update`

## Usage

```
use inhere\redis\RedisFactory;

$config = [
    'host' => 'redis',
    'port' => 6379,
    'timeout' => 0.0,
    'database' => 0,
];

$client = RedisFactory::createClient($config);

echo $redis->ping(); // +PONG
```

## Document

More see [document](document.md)
