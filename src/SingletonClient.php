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
    const MODE = 'singleton';

    /**
     * @var array
     */
    protected static $defaultConfig = [
        'host'  => '127.0.0.1',
        'port'  => '6379',
        'timeout' => 0.0,
        'database' => '0',
        'options'  => []
    ];

    /**
     * @param null $name
     * @return \Redis
     */
    protected function getConnection($name = null)
    {
        return parent::getConnection(self::MODE);
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        if ( $config ) {
            $this->config[self::MODE] = array_merge(self::$defaultConfig, $config);

            $this->setCallback(self::MODE);
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
            // trigger before event (read)
            $this->fireEvent(self::BEFORE_EXECUTE, [$upperMethod, 'unknown', [ 'args' => $args ]]);

            return call_user_func_array([$this->getConnection(), $upperMethod], $args);
        }

        throw new UnknownMethodException("Call the method [$method] don't exists!");
    }
}
