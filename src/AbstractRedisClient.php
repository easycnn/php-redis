<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/9/22
 * Time: 上午12:33
 */

namespace inhere\redis;

/**
 * Class AbstractRedisClient
 * @package inhere\redis
 */
abstract class AbstractRedisClient implements ClientInterface
{
    use SupportedCommandsTrait;

    protected $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }


    public function getSupportedCommands()
    {
        return array_merge(SupportedCommandsTrait::getSupportedCommands(),[
            'SHUTDOWN' => false,
            'INFO' => false,
            'DBSIZE' => false,
            'LASTSAVE' => false,
            'CONFIG' => false,
            'MONITOR' => false,
            'SLAVEOF' => false,
            'SAVE' => false,
            'BGSAVE' => false,
            'BGREWRITEAOF' => false,
            'SLOWLOG' => false,
        ]);
    }

}
