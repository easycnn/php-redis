<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/9/22
 * Time: 上午12:33
 */

namespace inhere\redis;

/**
 * Class RedisFactory
 * @package inhere\redis
 */
class RedisFactory
{
    const MODE_SINGLETON    = 1;
    const MODE_MASTER_SLAVE = 2;
    const MODE_CLUSTER      = 3;

    /**
     * createClient
     * @param  array   $config
     * @param  integer $mode
     * @return AbstractRedisClient
     */
    public static function createClient(array $config, $mode = 1)
    {
        if ($mode === self::MODE_CLUSTER) {
            return new ClusterClient($config);
        } elseif ($mode === self::MODE_MASTER_SLAVE) {
            return new MasterSlaveClient($config);
        }

        return new SingletonClient($config);
    }
}
