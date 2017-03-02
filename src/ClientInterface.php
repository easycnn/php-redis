<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2017/1/22
 * Time: 22:33
 */

namespace inhere\redis;


/**
 * Interface defining a client able to execute commands against Redis.
 */
interface ClientInterface
{
    // mode: singleton master-slave cluster
    const MODE = '';

    //
    // event lists
    // connect disconnect beforeExecute afterExecute
    //

    //
    const CONNECT = 'connect';
    const DISCONNECT = 'disconnect';

    const BEFORE_EXECUTE = 'beforeExecute';
    const AFTER_EXECUTE  = 'afterExecute'; // unused

    /**
     * @param null|string $name
     * @return \Redis
     */
    public function reader($name = null);

    /**
     * @param null|string $name
     * @return \Redis
     */
    public function writer($name = null);

    public function disconnect();

    public function fireEvent($event, array $args = []);

    public static function supportedEvents();

    /**
     * Returns the client options specified upon initialization.
     * @return array
     */
    public function getConfig();

    /**
     * Creates a Redis command with the specified arguments and sends a request to the server.
     * @param string $method    Command ID.
     * @param array  $arguments Arguments for the command.
     * @return mixed
     */
    public function __call($method, array $arguments);
}
