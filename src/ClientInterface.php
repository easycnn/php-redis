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
    /**
     * Returns the client options specified upon initialization.
     *
     * @return array
     */
    public function getConfig();

    /**
     * Opens the underlying connection to the server.
     */
    // public function connect();

    /**
     * Closes the underlying connection from the server.
     */
    public function disconnect();

    /**
     * Returns the underlying connection instance.
     *
     */
    // public function getConnection();

    /**
     * Creates a Redis command with the specified arguments and sends a request
     * to the server.
     *
     * @param string $method    Command ID.
     * @param array  $arguments Arguments for the command.
     *
     * @return mixed
     */
    public function __call($method, array $arguments);
}
