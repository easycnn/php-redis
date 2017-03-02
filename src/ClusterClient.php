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
    const MODE = 'cluster';

    /**
     * @inheritdoc
     */
    public function __call($method, array $args)
    {
        $upperMethod = strtoupper($method);

        // exists and enabled
        if (
            isset($this->getSupportedCommands()[$upperMethod]) &&
            true === $this->getSupportedCommands()[$upperMethod]
        ) {
            // trigger before event
            $this->fireEvent(self::BEFORE_EXECUTE, [$upperMethod, 'unknown', [ 'args' => $args ]]);

            return call_user_func_array([$this->getConnection(), $upperMethod], $args);
        }

        throw new UnknownMethodException("Call the method [$method] don't exists!");
    }
}
