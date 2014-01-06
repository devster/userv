<?php

namespace Userv\Connection;

interface ConnectionInterface
{
    /**
     * Set the server instance rerefence
     *
     * @param  Userv\Server $server
     */
    public function setServer(\Userv\Server $server);

    /**
     * Set the socket connection, created by stream_socket_accept
     *
     * @param resource $connection
     */
    public function setSocketConnection($connection);

    /**
     * Read on the socket connection
     *
     * @return string
     */
    public function read();

    /**
     * Write on the socket connection
     *
     * @param  string $msg
     */
    public function write($msg);

    /**
     * Close the socket connection
     */
    public function close();
}
