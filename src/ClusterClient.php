<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/2 0002
 * Time: 22:33
 * @referrer https://github.com/auraphp/Aura.Sql
 * File: ClusterClient.php
 */

namespace inhere\redis;

/**
 * $client = new ClusterClient($config);
 * $config = [
 *     'name1' => [
 *         'host' => '127.0.0.1',
 *         'port' => '6379',
 *         'database' => '0',
 *         'options' => []
 *     ],
 *     'name2' => [],
 *     ...
 * ];
 */
class ClusterClient extends AbstractRedisClient
{
    /**
     * connection callback list
     * @var array
     */
    private $values = [
        // 'name1' => function(){},
        // 'name2' => function(){},
    ];

    /**
     * instaneced connections
     * @var \Redis[]
     */
    private $connections = [
        // 'name1' => Object (\Redis),
        // 'name2' => Object (\Redis),
    ];

    public function __construct(array $config)
    {
        parent::__construct($config);

        if ($config) {
            $this->setConnections($config);
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
            return call_user_func_array([$this->getConnection(), $upperMethod], $args);
        }

        throw new UnknownMethodException("Call the method [$method] don't exists!");
    }

    public function disconnect()
    {
        $this->connections = [];
    }

    public function setConnections(array $config)
    {
        foreach ($config as $name => $conf) {
            $this->setConnection($name, $conf);
        }
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
    protected function getConnection($name = null)
    {
        $types = $type . 's';

        // no config, return defualt
        if ( !$this->config ) {
            throw new \RuntimeException('No connection config for connect the redis');
        }

        if ( null === $name ) {
            // return a random reader
            $name = array_rand($this->config);
        }

        if ( !isset($this->config[$name]) ) {
            throw new \InvalidArgumentException("The connection [$name] don't exists!");
        }

        // if not be instanced.
        if ( !isset($this->connections[$key]) ) {
            $this->connections[$key] = $this->values[$key]();
        }

        return $this->connections[$key];
    }

}
