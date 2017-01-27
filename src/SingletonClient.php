<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/2 0002
 * Time: 22:33
 * File: SingletonClient.php
 */

namespace inhere\redis;

/**
 * $client = new SingletonClient($config);
 * $config = [
 *     'host' => '127.0.0.1',
 *     'port' => '6379',
 *     'database' => '0',
 *     'options' => []
 * ];
 */
class SingletonClient extends AbstractRedisClient
{
    /**
     * @var \Closure
     */
    private $value;

    /**
     * instanced connection
     * @var \Redis
     */
    private $connection;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        if ($config) {
            $this->setConnection($config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, array $args)
    {
        $upperMethod = strtoupper($method);

        // exists and enabled
        if (
            isset($this->getSupportedCommands()[$upperMethod]) &&
            true === $this->getSupportedCommands()[$upperMethod]
        ) {
            return call_user_func_array([$this->connection, $upperMethod], $args);
        }

        throw new UnknownMethodException("Call the method [$method] don't exists!");
    }

    public function disconnect()
    {
        $this->connection = null;
    }

    /**
     * @param array $config
     */
    public function setConnection(array $config)
    {
        $this->value = function() use ($config)
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
     * @return \Redis
     */
    public function getConnection()
    {
        if (!$this->connection) {
            if ( $cb = $this->value ) {
                throw new \InvalidArgumentException('No config for connect the redis');
            }

            $this->connection = $cb();
        }

        return $this->connection;
    }

}
