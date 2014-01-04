<?php

namespace Userv;

/**
 * This class represent a connection with a unique client
 */
class Connection
{
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function write($msg)
    {
        fwrite($this->connection, $msg);
    }

    public function writeln($msg)
    {
        $this->write($msg."\n");
    }

    public function overwrite($msg)
    {
        $this->write("\x0D");
        $this->write($msg);
    }

    public function close()
    {
        fclose($this->connection);
    }
}
