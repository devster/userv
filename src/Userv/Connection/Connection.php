<?php

namespace Userv\Connection;

use Userv\Server;

/**
 * This class represent a connection with a unique client
 */
class Connection implements ConnectionInterface
{
    public $connection;

    public $server;

    /**
     * {@inheritdoc}
     */
    public function setServer(Server $server)
    {
        $this->server = $server;
    }

    /**
     * {@inheritdoc}
     */
    public function setSocketConnection($connection)
    {
        if (! is_resource($connection)) {
            throw new \InvalidArgumentException('Connection must be a resource');
        }

        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        return trim(fgets($this->connection));
    }

    /**
     * {@inheritdoc}
     */
    public function write($msg)
    {
        fwrite($this->connection, $msg);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        fclose($this->connection);
    }
}
