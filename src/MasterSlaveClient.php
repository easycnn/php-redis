<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2017/1/22
 * Time: 22:33
 * @referrer https://github.com/auraphp/Aura.Sql
 * File: MasterSlaveClient.php
 */

namespace inhere\redis;

/**
 * $client = new MasterSlaveClient($config);
 * $config = [
 *     'master' => [
 *         'host' => '127.0.0.1',
 *         'port' => '6379',
 *         'database' => '0',
 *         'options' => []
 *     ],
 *     'slaves' => [
 *         'slave1' => [
 *             'host' => '127.0.0.1',
 *             'port' => '6379',
 *             'database' => '0',
 *             'options' => []
 *         ],
 *         'slave2' => [],
 *         'slave3' => [],
 *         ...
 *     ],
 * ]
 */
class MasterSlaveClient extends AbstractRedisClient
{
    const TYPE_WRITER = 'writer';
    const TYPE_READER = 'reader';

    /**
     * connection callback list
     * @var array
     */
    private $values = [
        // 'writer.master' => function(){},
        // 'reader.slave1' => function(){},
    ];

    /**
     * instaneced connections
     * @var \Redis[]
     */
    private $connections = [
        // 'writer.master' => Object (\Redis),
    ];

    protected $config = [
        'writers' => [],
        'readers' => [
            // slave1 ...
            // slave2 ...
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config)
    {
        parent::__construct([]);

        if ( isset($config['master']) ) {
            $this->setWriter('master', $config['master']);
        }

        if ( isset($config['slaves']) ) {
            foreach ($config['slaves'] as $name => $reader) {
                $this->setReader($name, $reader);
            }
        }
    }

    public function __call($method, array $args)
    {
        $upperMethod = strtoupper($method);

        // exists and enabled
        if (
            isset($this->getSupportedCommands()[$upperMethod]) &&
            true === $this->getSupportedCommands()[$upperMethod]
        ) {
            return $this->execByMethod($upperMethod, $args);
        }

        throw new UnknownMethodException("Call the method [$method] don't exists!");
    }

    public function execByMethod($upperMethod, $args)
    {
        if (
            isset($this->getReadOnlyOperations()[$upperMethod]) &&
            $value = $this->getReadOnlyOperations()[$upperMethod] &&
            is_array($value) &&
            call_user_func($value, $args)
        ) {
            return call_user_func_array([$this->getReader(), $upperMethod], $args);
        }

        return call_user_func_array([$this->getWriter(), $upperMethod], $args);
    }

    public function disconnect()
    {
        $this->connections = [];
    }

    /**
     * set Writer
     * @param string   $name
     * @param array $cb
     */
    public function setWriter($name, array $config)
    {
        $this->config['writers'][$name] = $config;
        $this->setConnection("writer.{$name}", $config);
    }

    /**
     * get Writer
     * @param  string $name
     * @return AbstractDriver
     */
    public function getWriter($name = 'master')
    {
        return $this->getConnection(self::TYPE_WRITER, $name);
    }

    /**
     * setReader
     * @param string   $name
     * @param array $config
     */
    public function setReader($name, array $config)
    {
        $this->config['readers'][$name] = $config;
        $this->setConnection("reader.{$name}", $config);
    }

    /**
     * get Reader
     * @param  string $name
     * @return AbstractDriver
     */
    public function getReader($name = null)
    {
        return $this->getConnection(self::TYPE_READER, $name);
    }

    public function setConnection($name, array $config)
    {
        $this->value[$name] = function() use ($config)
        {
            $client = new \Redis();
            $client->connect($config['host'], $config['port'], $config['timeout']);
            $database = isset($config['database']) ? $config['database'] : 0;
            $client->select((int)$database);

            $options = isset($config['options']) && is_array($config['options']) ? $config['options']:[];
            foreach($options as $name => $value) {
                $client->setOption($name, $value);
            }

            return $client;
        };
    }

    /**
     * getConnection
     * @param  string $type
     * @param  string $name
     * @return \Redis
     */
    protected function getConnection($type, $name)
    {
        $types = $type . 's';

        // no reader/writer, return defualt
        if ( !$this->config[$types] ) {
            throw new \RuntimeException('No connection for type:' . $types);
        }

        if ( null === $name ) {
            // return a random reader
            $name = array_rand($this->config[$types]);
        }

        $key = $type . '.' . $name;

        if ( !isset($this->config[$types][$name]) ) {
            throw new \InvalidArgumentException("The connection [$type: $name] don't exists!");
        }

        // if not be instanced.
        if ( !isset($this->connections[$key]) ) {
            $this->connections[$key] = $this->values[$key]();
        }

        return $this->connections[$key];
    }

    public function getValue($name, $type = self::TYPE_READER)
    {
        if ( !isset($this->config[$type][$name]) ) {
            throw new \InvalidArgumentException("The connection [$type: $name] don't exists!");
        }

        return $this->values[$type][$name];
    }

    /**
     * Checks if a SORT command is a readable operation by parsing the arguments
     * array of the specified commad instance.
     *
     * @param array $arguments Command instance.
     *
     * @return bool
     */
    protected function isSortReadOnly(array $arguments)
    {
        $argc = count($arguments);

        if ($argc > 1) {
            for ($i = 1; $i < $argc; ++$i) {
                $argument = strtoupper($arguments[$i]);
                if ($argument === 'STORE') {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Checks if BITFIELD performs a read-only operation by looking for certain
     * SET and INCRYBY modifiers in the arguments array of the command.
     *
     * @param array $arguments Command instance.
     *
     * @return bool
     */
    protected function isBitfieldReadOnly(array $arguments)
    {
        $argc = count($arguments);

        if ($argc >= 2) {
            for ($i = 1; $i < $argc; ++$i) {
                $argument = strtoupper($arguments[$i]);
                if ($argument === 'SET' || $argument === 'INCRBY') {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Checks if a GEORADIUS command is a readable operation by parsing the
     * arguments array of the specified commad instance.
     *
     * @param array $arguments Command instance.
     *
     * @return bool
     */
    protected function isGeoradiusReadOnly(array $arguments)
    {
        $argc = count($arguments);
        $startIndex = $command->getId() === 'GEORADIUS' ? 5 : 4;

        if ($argc > $startIndex) {
            for ($i = $startIndex; $i < $argc; ++$i) {
                $argument = strtoupper($arguments[$i]);
                if ($argument === 'STORE' || $argument === 'STOREDIST') {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Returns the default list of commands performing read-only operations.
     *
     * @return array
     */
    protected function getReadOnlyOperations()
    {
        return array(
            'EXISTS' => true,
            'TYPE' => true,
            'KEYS' => true,
            'SCAN' => true,
            'RANDOMKEY' => true,
            'TTL' => true,
            'GET' => true,
            'MGET' => true,
            'SUBSTR' => true,
            'STRLEN' => true,
            'GETRANGE' => true,
            'GETBIT' => true,
            'LLEN' => true,
            'LRANGE' => true,
            'LINDEX' => true,
            'SCARD' => true,
            'SISMEMBER' => true,
            'SINTER' => true,
            'SUNION' => true,
            'SDIFF' => true,
            'SMEMBERS' => true,
            'SSCAN' => true,
            'SRANDMEMBER' => true,
            'ZRANGE' => true,
            'ZREVRANGE' => true,
            'ZRANGEBYSCORE' => true,
            'ZREVRANGEBYSCORE' => true,
            'ZCARD' => true,
            'ZSCORE' => true,
            'ZCOUNT' => true,
            'ZRANK' => true,
            'ZREVRANK' => true,
            'ZSCAN' => true,
            'ZLEXCOUNT' => true,
            'ZRANGEBYLEX' => true,
            'ZREVRANGEBYLEX' => true,
            'HGET' => true,
            'HMGET' => true,
            'HEXISTS' => true,
            'HLEN' => true,
            'HKEYS' => true,
            'HVALS' => true,
            'HGETALL' => true,
            'HSCAN' => true,
            'HSTRLEN' => true,
            'PING' => true,
            'AUTH' => true,
            'SELECT' => true,
            'ECHO' => true,
            'QUIT' => true,
            'OBJECT' => true,
            'BITCOUNT' => true,
            'BITPOS' => true,
            'TIME' => true,
            'PFCOUNT' => true,
            'SORT' => array($this, 'isSortReadOnly'),
            'BITFIELD' => array($this, 'isBitfieldReadOnly'),
            'GEOHASH' => true,
            'GEOPOS' => true,
            'GEODIST' => true,
            'GEORADIUS' => array($this, 'isGeoradiusReadOnly'),
            'GEORADIUSBYMEMBER' => array($this, 'isGeoradiusReadOnly'),
        );
    }
}
