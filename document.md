# php-redis

## config

## singleton config

```
$config = [
    'host' => 'redis',
    'port' => 6379,
    'timeout' => 0.0,
    'database' => 0,
];
```

### master-slave config

```
$config = [
      'mode' => 2, // 1 singleton 2 master-slave 3 cluster
      'master' => [
          'host' => 'redis',
          'port' => 6379,
          'timeout' => 0.0,
          'database' => 0,
      ],
      'slaves' => [
          'slave0' => [
              'host' => 'redis',
              'port' => 6380,
              'timeout' => 0.0,
              'database' => 0,
          ]
      ],
    ];
```

### cluster config

```
$config = [
     'name1' => [
         'host' => '127.0.0.1',
         'port' => '6379',
         'database' => '0',
         'options' => []
     ],
     'name2' => [
         'host' => '127.0.0.2',
         'port' => '6379',
         'database' => '0',
         'options' => []
     ],
];
```

## create client 

```
use inhere\redis\RedisFactory;

$mode = 1;

if ( isset($config['mode']) ) {
    $mode = (int)$config['mode'];
    unset($config['mode']);
}

$redis = RedisFactory::createClient($config, $mode);
```

## usage

```
echo $redis->ping(); // +PONG
```
